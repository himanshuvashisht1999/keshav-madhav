/**
 * SnapKid Agent Offline Database & Sync Engine (IndexedDB + Cache API)
 */
const SnapKidOfflineDB = (function () {
    const DB_NAME = 'SnapKidOfflineDB';
    const DB_VERSION = 1;
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

    // Save full catalog
    async function saveCatalogData(data, onProgress) {
        const db = await openDB();

        // 1. Save Shops
        if (data.shops && data.shops.length > 0) {
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

        // 2. Save Catalog Items
        if (data.products && data.products.length > 0) {
            await new Promise((resolve, reject) => {
                const tx = db.transaction('catalog', 'readwrite');
                const store = tx.objectStore('catalog');
                store.clear();
                for (const prod of data.products) {
                    store.put(prod);
                }
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        // 3. Save Meta info & Barcode index
        await new Promise((resolve, reject) => {
            const metaTx = db.transaction('meta', 'readwrite');
            const metaStore = metaTx.objectStore('meta');
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
                see_price: (data.see_price === true || data.see_price === 1)
            });
            metaTx.oncomplete = () => resolve();
            metaTx.onerror = (e) => reject(e.target.error);
        });

        // 4. Pre-cache product images in Cache Storage
        if (data.image_urls && data.image_urls.length > 0 && 'caches' in window) {
            try {
                const cache = await caches.open('snapkid-product-images');
                const total = data.image_urls.length;
                let cachedCount = 0;

                for (let i = 0; i < total; i++) {
                    const url = data.image_urls[i];
                    try {
                        const match = await cache.match(url);
                        if (!match) {
                            await cache.add(url);
                        }
                    } catch (imgErr) {
                        // ignore individual image failure
                    }
                    cachedCount++;
                    if (onProgress && (cachedCount % 10 === 0 || cachedCount === total)) {
                        onProgress(cachedCount, total);
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

    async function getBarcodeMap() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('meta', 'readonly');
            const req = tx.objectStore('meta').get('barcodes');
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

    async function getCatalog() {
        const db = await openDB();
        return new Promise((resolve) => {
            const tx = db.transaction('catalog', 'readonly');
            const req = tx.objectStore('catalog').getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => resolve([]);
        });
    }

    async function findVariationByBarcode(barcode) {
        if (!barcode) return null;
        let code = String(barcode).trim();
        if (!code) return null;

        // 1. If barcode is a URL (e.g. https://domain.com/fc/F123), extract the barcode value
        if (code.includes('/fc/')) {
            const parts = code.split('/fc/')[1];
            code = parts.split('/')[0].split('?')[0].split('#')[0].trim();
        } else if (code.startsWith('http://') || code.startsWith('https://')) {
            const cleanUrl = code.split('?')[0].split('#')[0];
            const parts = cleanUrl.split('/');
            code = parts[parts.length - 1].trim();
        }

        const catalog = await getCatalog();
        const barcodesMeta = await getBarcodeMap();

        // 2. Direct exact barcode match in catalog (e.g. D116S4C2)
        const exactMatch = catalog.find(item => item.barcode && item.barcode.toUpperCase() === code.toUpperCase());
        if (exactMatch) {
            return Object.assign({}, exactMatch, {
                type: 'single',
                item: exactMatch,
                items: [exactMatch]
            });
        }

        // 3. Lookup in barcodes mapping (Fair Products, Variant Colors, Sample Products)
        const mapped = barcodesMeta[code] || barcodesMeta[code.toUpperCase()] || barcodesMeta[code.toLowerCase()];
        if (mapped) {
            // If mapped barcode specifies an exact color variation
            if (mapped.color_id) {
                const specificVar = catalog.find(item =>
                    item.product_id == mapped.product_id &&
                    item.size_set_id == mapped.size_set_id &&
                    item.color_id == mapped.color_id
                );
                if (specificVar) {
                    return Object.assign({}, specificVar, {
                        type: 'single',
                        item: specificVar,
                        items: [specificVar]
                    });
                }
            }

            // If sample tag or fair product (which specifies product_id and size_set_id)
            const matchingVars = catalog.filter(item =>
                item.product_id == mapped.product_id &&
                (!mapped.size_set_id || item.size_set_id == mapped.size_set_id)
            );
            if (matchingVars.length > 0) {
                const isSingle = matchingVars.length === 1;
                return Object.assign({}, matchingVars[0], {
                    type: isSingle ? 'single' : 'multiple',
                    item: matchingVars[0],
                    items: matchingVars
                });
            }

            // Fallback: match by product_id only
            const productVars = catalog.filter(item => item.product_id == mapped.product_id);
            if (productVars.length > 0) {
                return Object.assign({}, productVars[0], {
                    type: productVars.length === 1 ? 'single' : 'multiple',
                    item: productVars[0],
                    items: productVars
                });
            }
        }

        // 4. Try regex match: D{productId}S{sizeSetId}C{colorId} (e.g. D116S4C2 or D116S4C2P1F1)
        const matchD = code.match(/^D(\d+)S(\d+)C(\d+)/i);
        if (matchD) {
            const pId = parseInt(matchD[1]);
            const sId = parseInt(matchD[2]);
            const cId = parseInt(matchD[3]);
            const foundD = catalog.find(item => item.product_id == pId && item.size_set_id == sId && item.color_id == cId);
            if (foundD) {
                return Object.assign({}, foundD, {
                    type: 'single',
                    item: foundD,
                    items: [foundD]
                });
            }
        }

        // 5. Try regex match: FAIR-{productId}-{sizeSetId}
        const matchFair = code.match(/^FAIR-(\d+)-(\d+)/i);
        if (matchFair) {
            const pId = parseInt(matchFair[1]);
            const sId = parseInt(matchFair[2]);
            const fairVars = catalog.filter(item => item.product_id == pId && item.size_set_id == sId);
            if (fairVars.length > 0) {
                return Object.assign({}, fairVars[0], {
                    type: fairVars.length === 1 ? 'single' : 'multiple',
                    item: fairVars[0],
                    items: fairVars
                });
            }
        }

        // 6. Try match by design number (e.g. 116, #116, D116)
        const cleanDesign = code.replace(/^[#dD]/, '').trim();
        const designMatches = catalog.filter(item =>
            (item.design_number && item.design_number.toLowerCase() === code.toLowerCase()) ||
            (cleanDesign && item.design_number && item.design_number.toLowerCase() === cleanDesign.toLowerCase())
        );
        if (designMatches.length > 0) {
            return Object.assign({}, designMatches[0], {
                type: designMatches.length === 1 ? 'single' : 'multiple',
                item: designMatches[0],
                items: designMatches
            });
        }

        return null;
    }

    async function saveOfflineOrder(orderData) {
        const db = await openDB();
        orderData.local_id = 'OFF_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        orderData.created_at = new Date().toISOString();
        orderData.status = 'pending_sync';

        return new Promise((resolve, reject) => {
            const tx = db.transaction('offline_orders', 'readwrite');
            const req = tx.objectStore('offline_orders').add(orderData);
            req.onsuccess = () => resolve(orderData);
            req.onerror = () => reject(req.error);
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
        getBarcodeMap,
        getShops,
        getCatalog,
        findVariationByBarcode,
        saveOfflineOrder,
        getPendingOrders,
        removeOfflineOrder,
        markOrderSynced
    };
})();
window.SnapKidOfflineDB = SnapKidOfflineDB;
