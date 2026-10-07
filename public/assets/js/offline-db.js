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

        // 3. Save Meta info
        await new Promise((resolve, reject) => {
            const metaTx = db.transaction('meta', 'readwrite');
            const metaStore = metaTx.objectStore('meta');
            metaStore.put({
                key: 'info',
                last_synced: new Date().toISOString(),
                shops_count: data.shops ? data.shops.length : 0,
                products_count: data.products ? data.products.length : 0,
                settings: data.settings || {},
                sales_men: data.sales_men || [],
                see_price: data.see_price !== undefined ? data.see_price : true
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
        const db = await openDB();
        const catalog = await getCatalog();
        
        // Exact barcode match
        let found = catalog.find(item => item.barcode === barcode);
        if (found) return found;

        // Try regex match D{productId}S{sizeSetId}C{colorId}
        const match = barcode.match(/^D(\d+)S(\d+)C(\d+)/i);
        if (match) {
            const pId = parseInt(match[1]);
            const sId = parseInt(match[2]);
            const cId = parseInt(match[3]);
            found = catalog.find(item => item.product_id == pId && item.size_set_id == sId && item.color_id == cId);
            if (found) return found;
        }

        // Try design number match
        found = catalog.find(item => item.design_number && item.design_number.toLowerCase() === barcode.toLowerCase());
        return found || null;
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
