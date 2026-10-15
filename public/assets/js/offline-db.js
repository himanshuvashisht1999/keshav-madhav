/**
 * SnapKid Agent Offline Database & Sync Engine (IndexedDB + Cache API)
 */
const SnapKidOfflineDB = (function () {
    const DB_NAME = 'SnapKidOfflineDB';
    const DB_VERSION = 2;
    let dbInstance = null;

    function openDB() {
        if (dbInstance) return Promise.resolve(dbInstance);
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onupgradeneeded = function (e) {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('catalog')) {
                    const catalogStore = db.createObjectStore('catalog', { keyPath: 'key' });
                    catalogStore.createIndex('design_number', 'design_number', { unique: false });
                    catalogStore.createIndex('barcode', 'barcode', { unique: false });
                    catalogStore.createIndex('product_id', 'product_id', { unique: false });
                }
                if (!db.objectStoreNames.contains('shops')) {
                    db.createObjectStore('shops', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('offline_orders')) {
                    const orderStore = db.createObjectStore('offline_orders', { keyPath: 'local_id' });
                    orderStore.createIndex('status', 'status', { unique: false });
                    orderStore.createIndex('created_at', 'created_at', { unique: false });
                }
                if (!db.objectStoreNames.contains('meta')) {
                    db.createObjectStore('meta', { keyPath: 'key' });
                }
                if (!db.objectStoreNames.contains('images_cache')) {
                    db.createObjectStore('images_cache', { keyPath: 'key' });
                }
            };
            request.onsuccess = function (e) {
                dbInstance = e.target.result;
                resolve(dbInstance);
            };
            request.onerror = function (e) {
                reject(e.target.error);
            };
        });
    }

    // Helper: Store image blob in IndexedDB images_cache
    async function storeImageBlob(db, key, blob) {
        return new Promise((resolve) => {
            try {
                const tx = db.transaction('images_cache', 'readwrite');
                tx.objectStore('images_cache').put({ key: key, blob: blob, cached_at: Date.now() });
                tx.oncomplete = () => resolve(true);
                tx.onerror = () => resolve(false);
            } catch (_) {
                resolve(false);
            }
        });
    }

    // Helper: Get image blob from IndexedDB images_cache
    async function getImageBlob(key) {
        const db = await openDB();
        return new Promise((resolve) => {
            try {
                const tx = db.transaction('images_cache', 'readonly');
                const req = tx.objectStore('images_cache').get(key);
                req.onsuccess = () => resolve(req.result ? req.result.blob : null);
                req.onerror = () => resolve(null);
            } catch (_) {
                resolve(null);
            }
        });
    }

    // Helper: Create Object URL for cached image if available
    async function getImageBlobUrl(urlOrName) {
        if (!urlOrName) return null;
        const filename = urlOrName.split('/').pop().split('?')[0];

        // 1. Check IndexedDB blob cache (by filename or full url)
        let blob = await getImageBlob(filename);
        if (!blob) {
            blob = await getImageBlob(urlOrName);
        }
        if (blob) {
            return URL.createObjectURL(blob);
        }

        // 2. Check Cache API
        if ('caches' in window) {
            try {
                const cache = await caches.open('snapkid-product-images');
                let match = await cache.match(urlOrName);
                if (!match && !urlOrName.startsWith('http') && !urlOrName.startsWith('/')) {
                    const fullAssetUrl = window.location.origin + '/assets/products/' + filename;
                    match = await cache.match(fullAssetUrl);
                }
                if (match) {
                    const resBlob = await match.blob();
                    return URL.createObjectURL(resBlob);
                }
            } catch (_) {}
        }

        return null;
    }

    // Save full catalog
    async function getUnsyncedOrders() {
        const all = await getPendingOrders();
        return all.filter(o => o.status === 'pending_sync');
    }

    async function markOrderError(local_id, message) {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('offline_orders', 'readwrite');
            const store = tx.objectStore('offline_orders');
            const g = store.get(local_id);
            g.onsuccess = () => {
                if (g.result) { g.result.last_error = message || null; store.put(g.result); }
                resolve(true);
            };
            g.onerror = () => resolve(false);
        });
    }

    async function clearSyncedOrders() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('offline_orders', 'readwrite');
            const store = tx.objectStore('offline_orders');
            const req = store.getAll();
            req.onsuccess = () => {
                (req.result || []).forEach(o => { if (o.status === 'synced') store.delete(o.local_id); });
            };
            tx.oncomplete = () => resolve(true);
            tx.onerror = () => resolve(false);
        });
    }

    async function saveCatalogData(data, onProgress) {
        // Hard rule (no force): never wipe while orders are still waiting to be uploaded
        const waiting = await getUnsyncedOrders();
        if (waiting.length > 0) {
            const err = new Error('You have ' + waiting.length + ' offline order(s) not uploaded yet. Upload or discard them first.');
            err.code = 'PENDING_ORDERS';
            throw err;
        }
        await clearSyncedOrders();
        const db = await openDB();

        // Fresh start: wipe all previously synced data (NOT pending offline orders), then re-save everything.
        // Runs only after the new data has been downloaded, so a failed download never leaves the phone empty.
        await new Promise((resolve, reject) => {
            const stores = ['catalog', 'shops', 'meta', 'images_cache'];
            const tx = db.transaction(stores, 'readwrite');
            stores.forEach(n => tx.objectStore(n).clear());
            tx.oncomplete = () => resolve();
            tx.onerror = (e) => reject(e.target.error);
        });
        if ('caches' in window) {
            try { await caches.delete('snapkid-product-images'); } catch (_) {}
        }

        // 1. Save Shops
        if (data.shops) {
            await new Promise((resolve, reject) => {
                const tx = db.transaction('shops', 'readwrite');
                const store = tx.objectStore('shops');
                store.clear();
                for (const shop of data.shops) {
                    store.put(shop);
                }
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        // 2. Save Catalog Products (Bulk via Transaction)
        if (data.products) {
            await new Promise((resolve, reject) => {
                const tx = db.transaction('catalog', 'readwrite');
                const store = tx.objectStore('catalog');
                store.clear();
                for (const item of data.products) {
                    store.put(item);
                }
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        // 3. Save Barcodes Map & Meta Info
        await new Promise((resolve, reject) => {
            const metaTx = db.transaction('meta', 'readwrite');
            const metaStore = metaTx.objectStore('meta');
            metaStore.put({
                key: 'scan_payloads',
                map: data.scan_payloads || {}
            });
            metaStore.put({
                key: 'barcodes',
                map: data.barcodes || {}
            });
            metaStore.put({
                key: 'info',
                last_synced: new Date().toISOString(),
                shops_count: data.shops ? data.shops.length : 0,
                products_count: data.products ? data.products.length : 0,
                settings: data.settings || {},
                sales_men: data.sales_men || [],
                see_price: (data.see_price === true || data.see_price === 1),
                // server time of this download: tells whether this price permission is newer than a page's
                server_time: data.timestamp || null
            });
            metaTx.oncomplete = () => resolve();
            metaTx.onerror = (e) => reject(e.target.error);
        });

        // 4. Pre-cache product images in Cache Storage and IndexedDB
        const urlsToCache = data.image_urls || [];
        if (urlsToCache.length > 0) {
            try {
                let cache = null;
                if ('caches' in window) {
                    try {
                        cache = await caches.open('snapkid-product-images');
                    } catch (_) {}
                }

                const total = urlsToCache.length;
                let cachedCount = 0;
                const batchSize = 10;

                for (let i = 0; i < total; i += batchSize) {
                    const batch = urlsToCache.slice(i, i + batchSize);
                    await Promise.all(batch.map(async (url) => {
                        const filename = url.split('/').pop().split('?')[0];
                        try {
                            if (cache) {
                                const match = await cache.match(url);
                                if (!match) {
                                    const resp = await fetch(url, { mode: 'cors' });
                                    if (resp && resp.ok) {
                                        await cache.put(url, resp.clone());
                                        const blob = await resp.blob();
                                        await storeImageBlob(db, filename, blob);
                                        await storeImageBlob(db, url, blob);
                                    }
                                } else {
                                    const blob = await match.blob();
                                    await storeImageBlob(db, filename, blob);
                                    await storeImageBlob(db, url, blob);
                                }
                            } else {
                                // No Cache API support: fetch directly and save blob in IndexedDB
                                const resp = await fetch(url);
                                if (resp && resp.ok) {
                                    const blob = await resp.blob();
                                    await storeImageBlob(db, filename, blob);
                                    await storeImageBlob(db, url, blob);
                                }
                            }
                        } catch (imgErr) {
                            // ignore individual photo fetch failure
                        }
                        cachedCount++;
                    }));

                    if (onProgress) {
                        onProgress(Math.min(cachedCount, total), total);
                    }
                }
            } catch (cacheErr) {
                console.warn('Image pre-caching failed:', cacheErr);
            }
        }

        return true;
    }

    async function getMeta() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('meta', 'readonly');
            const req = tx.objectStore('meta').get('info');
            req.onsuccess = () => resolve(req.result || null);
            req.onerror = () => resolve(null);
        });
    }

    /**
     * "Show pricing info" (sales agent setting in admin): the newest word wins - the page the server just sent
     * (pageTime = server time in ms when it made the page) or the last catalogue download. A newer page value is
     * saved on the phone, so pages opened later without internet agree with it.
     */
    async function resolveSeePrice(pageValue, pageTime) {
        pageValue = !!pageValue;
        let meta = null;
        try { meta = await getMeta(); } catch (e) {}
        if (!meta) return pageValue;
        const metaTime = meta.server_time ? Date.parse(meta.server_time) : 0;
        if (typeof meta.see_price !== 'undefined' && metaTime > pageTime) {
            return meta.see_price === true || meta.see_price === 1 || meta.see_price === '1';
        }
        if (meta.see_price !== pageValue || !meta.server_time) {
            try {
                const db = await openDB();
                await new Promise((resolve) => {
                    const tx = db.transaction('meta', 'readwrite');
                    meta.see_price = pageValue;
                    meta.server_time = new Date(pageTime).toISOString();
                    tx.objectStore('meta').put(meta);
                    tx.oncomplete = () => resolve();
                    tx.onerror = () => resolve();
                });
            } catch (e) {}
        }
        return pageValue;
    }

    async function getBarcodeMap() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('meta', 'readonly');
            const req = tx.objectStore('meta').get('barcodes');
            req.onsuccess = () => resolve((req.result && req.result.map) ? req.result.map : {});
            req.onerror = () => resolve({});
        });
    }

    async function getScanPayloads() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('meta', 'readonly');
            const req = tx.objectStore('meta').get('scan_payloads');
            req.onsuccess = () => resolve((req.result && req.result.map) ? req.result.map : {});
            req.onerror = () => resolve({});
        });
    }

    async function getShops() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('shops', 'readonly');
            const req = tx.objectStore('shops').getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => resolve([]);
        });
    }

    /**
     * Brand discounts of the shop currently chosen in the offline order screen
     * (each shop carries its own per-brand discounts, synced from the customer master).
     */
    async function getActiveBrandDiscounts() {
        try {
            const shopId = localStorage.getItem('last_offline_shop_id');
            if (!shopId) return {};
            const shops = await getShops();
            const shop = shops.find(s => String(s.id) === String(shopId));
            return (shop && shop.brand_discounts) || {};
        } catch (e) {
            return {};
        }
    }

    /**
     * Selling price for an item: fair/sample-batch items keep their fixed price,
     * everything else = MRP less the selected shop's brand discount.
     */
    function priceItem(item, discounts) {
        if (!item) return 0;
        if (item.fixed_price) return item.unit_price || 0;
        const d = parseFloat((discounts || {})[item.brand_id]) || 0;
        const mrp = parseFloat(item.mrp) || 0;
        return Math.ceil(mrp - (mrp * d / 100));
    }

    async function getCatalog() {
        const db = await openDB();
        const items = await new Promise((resolve) => {
            const tx = db.transaction('catalog', 'readonly');
            const req = tx.objectStore('catalog').getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => resolve([]);
        });
        const discounts = await getActiveBrandDiscounts();
        items.forEach(it => { it.unit_price = priceItem(it, discounts); });
        return items;
    }

    /**
     * Search product variation by barcode offline and format as matrix modal structure
     */
    async function findVariationByBarcode(rawCode) {
        if (!rawCode) return null;
        let code = String(rawCode).trim();

        // 1. If barcode is a URL, extract the barcode value
        if (code.includes('/fc/')) {
            const parts = code.split('/fc/')[1];
            code = parts.split('/')[0].split('?')[0].split('#')[0].trim();
        } else if (code.includes('/sc/')) {
            const parts = code.split('/sc/')[1];
            code = parts.split('/')[0].split('?')[0].split('#')[0].trim();
        } else if (code.startsWith('http://') || code.startsWith('https://')) {
            const cleanUrl = code.split('?')[0].split('#')[0];
            const parts = cleanUrl.split('/');
            code = parts[parts.length - 1].trim();
        }

        const barcodesMeta = await getBarcodeMap();

        // 1b. Fair / sample-set barcodes: use the pre-built payload (same as online scan)
        const fairMapped = barcodesMeta[code] || barcodesMeta[code.toUpperCase()] || barcodesMeta[code.toLowerCase()];
        if (fairMapped && fairMapped.payload_key) {
            const payloads = await getScanPayloads();
            const payload = payloads[fairMapped.payload_key];
            if (payload && payload.size_sets && payload.size_sets.length) {
                // sample-product payloads are not batch-priced: use the selected shop's brand discount
                const discounts = await getActiveBrandDiscounts();
                payload.size_sets.forEach(ss => {
                    if (ss.fixed_price) return;
                    ss.unit_price = priceItem(ss, discounts);
                    (ss.colors || []).forEach(c => { if (c.item) c.item.unit_price = priceItem(c.item, discounts); });
                });
                return Object.assign({}, payload, {
                    scanned_size_set_id: fairMapped.size_set_id || null,
                    scanned_color_id: fairMapped.color_id || null
                });
            }
        }

        const catalog = await getCatalog();
        if (!catalog || catalog.length === 0) return null;

        let targetProductId = null;
        let targetSizeSetId = null;
        let targetColorId = null;
        let matchedItem = null;

        // 2. Direct exact barcode match in catalog (e.g. D116S4C2)
        const exactMatch = catalog.find(item => item.barcode && item.barcode.toUpperCase() === code.toUpperCase());
        if (exactMatch) {
            targetProductId = exactMatch.product_id;
            targetSizeSetId = exactMatch.size_set_id;
            targetColorId = exactMatch.color_id;
            matchedItem = exactMatch;
        }

        // 3. Lookup in barcodes mapping (Variant Colors, Fair Products, Sample Products, Design Numbers)
        if (!targetProductId) {
            const mapped = barcodesMeta[code] || barcodesMeta[code.toUpperCase()] || barcodesMeta[code.toLowerCase()];
            if (mapped) {
                targetProductId = mapped.product_id;
                targetSizeSetId = mapped.size_set_id || null;
                targetColorId = mapped.color_id || null;
            }
        }

        // 4. Try regex match: D{productId}S{sizeSetId}C{colorId}
        if (!targetProductId) {
            const matchD = code.match(/^D(\d+)S(\d+)C(\d+)/i);
            if (matchD) {
                targetProductId = parseInt(matchD[1]);
                targetSizeSetId = parseInt(matchD[2]);
                targetColorId = parseInt(matchD[3]);
            }
        }

        // 5. Try regex match: FAIR-{productId}-{sizeSetId} or SMP-{productId}-{sizeSetId}
        if (!targetProductId) {
            const matchFs = code.match(/^(?:FAIR|SMP|SAMPLE)-(\d+)-(\d+)/i);
            if (matchFs) {
                targetProductId = parseInt(matchFs[1]);
                targetSizeSetId = parseInt(matchFs[2]);
            }
        }

        // 6. Try match by design number (e.g. 26069, #26069, D26069)
        if (!targetProductId) {
            const cleanDesign = code.replace(/^[#dD]/, '').trim();
            const designMatch = catalog.find(item =>
                (item.design_number && item.design_number.toLowerCase() === code.toLowerCase()) ||
                (cleanDesign && item.design_number && item.design_number.toLowerCase() === cleanDesign.toLowerCase())
            );
            if (designMatch) {
                targetProductId = designMatch.product_id;
                targetSizeSetId = designMatch.size_set_id;
            }
        }

        if (!targetProductId) return null;

        // Gather ALL variations for this product from offline catalog
        const allProductVars = catalog.filter(item => item.product_id == targetProductId);
        if (allProductVars.length === 0) return null;

        const first = allProductVars[0];
        const seriesName = first.series_name || '';
        const garmentName = first.name_of_garment ? String(first.name_of_garment).trim() : '';
        const prodTitle = (seriesName && garmentName && seriesName !== garmentName) ? `${seriesName} ${garmentName}` : (seriesName || garmentName || `Design #${first.design_number}`);

        // Find primary hero image
        let heroImage = '';
        for (const item of allProductVars) {
            if (item.image_url) {
                heroImage = item.image_url;
                break;
            }
        }

        // Group into size sets
        const sizeSetsMap = new Map();
        for (const item of allProductVars) {
            const sId = item.size_set_id;
            if (!sizeSetsMap.has(sId)) {
                sizeSetsMap.set(sId, {
                    size_set_id: sId,
                    size_set_name: item.size_set_name || `Size Set ${sId}`,
                    size_group: item.size_group || '',
                    mrp: item.mrp || 0,
                    unit_price: item.unit_price || 0,
                    image: item.image_url || null,
                    colors: []
                });
            }
            const ss = sizeSetsMap.get(sId);
            if (!ss.image && item.image_url) {
                ss.image = item.image_url;
            }
            // Add color
            ss.colors.push({
                id: item.color_id,
                name: item.color_name || `Color ${item.color_id}`,
                key: item.key,
                available_boxes: item.available_boxes || 0,
                is_advance_sample: !!item.is_advance_sample,
                pcs_per_box: item.pcs_per_box || 1,
                image: item.image_url || null
            });
        }

        const sizeSetsArray = Array.from(sizeSetsMap.values());

        // Sort so scanned size set is first
        if (targetSizeSetId) {
            sizeSetsArray.sort((a, b) => {
                if (a.size_set_id === targetSizeSetId) return -1;
                if (b.size_set_id === targetSizeSetId) return 1;
                return 0;
            });
        }

        return {
            success: true,
            product: {
                id: first.product_id,
                design_number: first.design_number || 'N/A',
                name: prodTitle,
                series_name: first.series_name || '',
                brand_name: first.brand_name || 'SNAPKID',
                image: heroImage || null,
                image_url: heroImage || null
            },
            scanned_size_set_id: targetSizeSetId,
            scanned_color_id: targetColorId,
            size_sets: sizeSetsArray,
            // Compatibility fields
            type: allProductVars.length === 1 ? 'single' : 'multiple',
            item: matchedItem || first,
            items: allProductVars
        };
    }

    // Save Offline Order
    async function saveOfflineOrder(orderData) {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            orderData.local_id = 'off_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
            orderData.created_at = new Date().toISOString();
            orderData.status = 'pending_sync';

            const tx = db.transaction('offline_orders', 'readwrite');
            const req = tx.objectStore('offline_orders').add(orderData);
            req.onsuccess = () => resolve(orderData);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function getPendingOrders() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('offline_orders', 'readonly');
            const req = tx.objectStore('offline_orders').getAll();
            req.onsuccess = () => {
                const orders = (req.result || []).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                resolve(orders);
            };
            req.onerror = () => resolve([]);
        });
    }

    async function removeOfflineOrder(local_id) {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('offline_orders', 'readwrite');
            const req = tx.objectStore('offline_orders').delete(local_id);
            req.onsuccess = () => resolve(true);
            req.onerror = () => resolve(false);
        });
    }

    async function markOrderSynced(local_id, serverDetails) {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('offline_orders', 'readwrite');
            const store = tx.objectStore('offline_orders');
            const getReq = store.get(local_id);
            getReq.onsuccess = () => {
                if (getReq.result) {
                    const order = getReq.result;
                    order.status = 'synced';
                    order.server_order_id = serverDetails.server_id || null;
                    order.server_order_no = serverDetails.order_no || null;
                    order.synced_at = new Date().toISOString();
                    store.put(order);
                }
                resolve(true);
            };
            getReq.onerror = () => resolve(false);
        });
    }

    return {
        openDB,
        saveCatalogData,
        getMeta,
        resolveSeePrice,
        getBarcodeMap,
        getScanPayloads,
        getShops,
        getActiveBrandDiscounts,
        priceItem,
        getCatalog,
        findVariationByBarcode,
        getImageBlobUrl,
        saveOfflineOrder,
        getPendingOrders,
        getUnsyncedOrders,
        markOrderError,
        clearSyncedOrders,
        removeOfflineOrder,
        markOrderSynced
    };
})();

window.SnapKidOfflineDB = SnapKidOfflineDB;
