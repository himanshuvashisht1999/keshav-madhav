/**
 * SnapKid agent app: data kept on the phone for orders without internet (IndexedDB + Cache API).
 *
 * - Everything on the phone belongs to one agent login (agent_id). Another agent on the same phone never sees,
 *   uploads or deletes it.
 * - Sync = 1) upload every waiting order (one at a time, JSON); the office answers saved / already saved / refused;
 *   saved ones are deleted from the phone, refused ones stay with the reason so the agent can fix them;
 *   2) download shops, products, stock, prices and sample sets fresh and replace everything that was on the phone.
 * - Uploads are safe to repeat: the office keeps each phone order number once (agent_offline_orders).
 */
const SnapKidOfflineDB = (function () {
    const DB_NAME = 'SnapKidOfflineDB';
    const DB_VERSION = 3;
    const IMAGE_CACHE = 'snapkid-product-images';
    const PAGE_CACHE_PREFIX = 'snapkid-agent-pages';
    const LOCK_KEY = 'sk_offline_sync_lock';
    const PAGES_KEY = 'sk_offline_pages_saved';
    let dbInstance = null;
    let cfg = { agentId: null, base: '/', urls: {} };

    function configure(c) {
        cfg = Object.assign({}, cfg, c || {});
        if (cfg.base && cfg.base.slice(-1) !== '/') cfg.base += '/';
    }

    function agentId() {
        return cfg.agentId != null ? String(cfg.agentId) : null;
    }

    // ---------- database ----------

    function openDB() {
        if (dbInstance) return Promise.resolve(dbInstance);
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onupgradeneeded = function (e) {
                const db = e.target.result;
                const tx = e.target.transaction;
                // data from older versions is not tied to an agent: catalogue data is dropped (the next Sync downloads it),
                // orders are kept and given at Sync to the agent whose shop they are for (see claimUnownedOrders)
                ['catalog', 'shops', 'meta', 'images_cache'].forEach(n => {
                    if (db.objectStoreNames.contains(n)) db.deleteObjectStore(n);
                });
                const catalog = db.createObjectStore('catalog', { keyPath: 'key' });
                catalog.createIndex('product_id', 'product_id', { unique: false });
                db.createObjectStore('shops', { keyPath: 'uid' });
                db.createObjectStore('meta', { keyPath: 'key' });
                const orders = db.objectStoreNames.contains('offline_orders')
                    ? tx.objectStore('offline_orders')
                    : db.createObjectStore('offline_orders', { keyPath: 'local_id' });
                if (!orders.indexNames.contains('agent_id')) orders.createIndex('agent_id', 'agent_id', { unique: false });
            };
            request.onsuccess = function (e) {
                dbInstance = e.target.result;
                dbInstance.onversionchange = () => { dbInstance.close(); dbInstance = null; };
                resolve(dbInstance);
            };
            request.onerror = function (e) { reject(e.target.error); };
            request.onblocked = function () { reject(new Error('Close the other SnapKid tabs and reload this page.')); };
        });
    }

    function req(r) {
        return new Promise((resolve, reject) => { r.onsuccess = () => resolve(r.result); r.onerror = () => reject(r.error); });
    }

    function done(tx) {
        return new Promise((resolve, reject) => { tx.oncomplete = () => resolve(); tx.onerror = () => reject(tx.error); tx.onabort = () => reject(tx.error || new Error('Phone storage write was cancelled.')); });
    }

    async function getAll(store) {
        const db = await openDB();
        return req(db.transaction(store, 'readonly').objectStore(store).getAll());
    }

    async function getOne(store, key) {
        const db = await openDB();
        return req(db.transaction(store, 'readonly').objectStore(store).get(key));
    }

    // ---------- catalogue (only the logged-in agent's own copy is ever returned) ----------

    async function metaRow(key) {
        const row = await getOne('meta', key);
        return row && String(row.agent_id) === agentId() ? row : null;
    }

    async function getMeta() {
        return metaRow('info');
    }

    async function getShops() {
        if (!(await getMeta())) return [];
        return getAll('shops');
    }

    async function getCatalog() {
        if (!(await getMeta())) return [];
        return getAll('catalog');
    }

    async function getBarcodeMap() { const r = await metaRow('barcodes'); return (r && r.map) || {}; }
    async function getScanPayloads() { const r = await metaRow('scan_payloads'); return (r && r.map) || {}; }
    async function getBlockedCodes() { const r = await metaRow('blocked_codes'); return (r && r.map) || {}; }
    async function getFairDiscounts() { const r = await metaRow('fair_discounts'); return (r && r.map) || {}; }

    /** Replace everything on the phone with a fresh download (one transaction: all or nothing). */
    async function replaceCatalog(data) {
        const db = await openDB();
        const owner = agentId();
        const tx = db.transaction(['catalog', 'shops', 'meta'], 'readwrite');
        const shops = tx.objectStore('shops');
        const catalog = tx.objectStore('catalog');
        const meta = tx.objectStore('meta');
        shops.clear();
        catalog.clear();
        meta.clear();
        (data.shops || []).forEach(s => shops.put(Object.assign({}, s, { uid: s.uid || ('customer_' + s.id), party_type: s.party_type || 'customer' })));
        (data.products || []).forEach(p => catalog.put(p));
        meta.put({ key: 'barcodes', agent_id: owner, map: data.barcodes || {} });
        meta.put({ key: 'scan_payloads', agent_id: owner, map: data.scan_payloads || {} });
        meta.put({ key: 'blocked_codes', agent_id: owner, map: data.blocked_codes || {} });
        meta.put({ key: 'fair_discounts', agent_id: owner, map: data.fair_discounts || {} });
        meta.put({
            key: 'info',
            agent_id: owner,
            agent_name: data.agent ? data.agent.name : null,
            last_synced: new Date().toISOString(),
            server_time: data.timestamp || null,
            timezone: data.timezone || cfg.timezone || 'Asia/Kolkata',
            shops_count: (data.shops || []).filter(s => (s.party_type || 'customer') === 'customer').length,
            vendors_count: (data.shops || []).filter(s => s.party_type === 'vendor').length,
            products_count: (data.products || []).length,
            settings: data.settings || {},
            sales_men: data.sales_men || [],
            see_price: (data.see_price === true || data.see_price === 1)
        });
        await done(tx);
    }

    /** API answers must never be kept (older app versions stored some): remove them and old app caches. */
    async function dropStoredApiCopies() {
        if (!('caches' in window)) return;
        try {
            const names = await caches.keys();
            for (const n of names) {
                if (/^snapkid-agent-v\d+$/.test(n)) { await caches.delete(n); continue; }
                if (n === IMAGE_CACHE) continue;
                const c = await caches.open(n);
                for (const r of await c.keys()) {
                    if (/\/agent\/(offline\/(catalog-data|session|sync)|orders\/get-variation-by-barcode)/.test(r.url)) await c.delete(r);
                }
            }
        } catch (_) {}
    }

    /** Photos: keep the ones still used (a changed photo always gets a new file name), delete the rest, fetch new ones. */
    async function refreshImages(urls, onProgress) {
        if (!('caches' in window)) return { kept: 0, fetched: 0, removed: 0, failed: 0 };
        const cache = await caches.open(IMAGE_CACHE);
        const wanted = new Set(urls || []);
        let removed = 0;
        const have = new Set();
        for (const r of await cache.keys()) {
            if (wanted.has(r.url)) have.add(r.url); else { await cache.delete(r); removed++; }
        }
        const missing = [...wanted].filter(u => !have.has(u));
        let fetched = 0, failed = 0, n = 0;
        const total = missing.length;
        for (let i = 0; i < total; i += 8) {
            await Promise.all(missing.slice(i, i + 8).map(async url => {
                try {
                    const resp = await fetch(url, { credentials: 'same-origin' });
                    if (resp && resp.ok) { await cache.put(url, resp); fetched++; } else { failed++; }
                } catch (_) { failed++; }
                n++;
            }));
            if (onProgress) onProgress(n, total);
        }
        return { kept: have.size, fetched, removed, failed };
    }

    async function getImageBlobUrl(urlOrName) {
        if (!urlOrName || !('caches' in window)) return null;
        try {
            const cache = await caches.open(IMAGE_CACHE);
            let match = await cache.match(urlOrName);
            if (!match) {
                const file = String(urlOrName).split('/').pop().split('?')[0];
                match = await cache.match(new URL(cfg.base + 'assets/products/' + file, location.href).href);
            }
            return match ? URL.createObjectURL(await match.blob()) : null;
        } catch (_) {
            return null;
        }
    }

    // ---------- prices and totals (same rounding as the office: AgentOrderRules) ----------

    function round4(x) { return Math.round(x * 10000) / 10000; }

    /** MRP less a discount %, rounded up (as the online order screen). */
    function priceAfter(mrp, pct) {
        mrp = parseFloat(mrp) || 0;
        pct = parseFloat(pct) || 0;
        return Math.ceil(round4(mrp * (100 - pct) / 100));
    }

    /** Order totals exactly like the office; error set when the office would refuse them. */
    function orderTotals(itemsValue, discount, otherCharges, gstPct) {
        const total = Math.ceil(round4(itemsValue));
        const d = Math.ceil(parseFloat(discount) || 0);
        const other = Math.ceil(parseFloat(otherCharges) || 0);
        let error = null;
        if (d < 0) error = 'Discount cannot be less than 0.';
        else if (other < 0) error = 'Other charges cannot be less than 0.';
        else if (d > total) error = 'Discount (₹' + d + ') cannot be more than the order value (₹' + total + ').';
        const taxable = total - d;
        const gst = Math.ceil(round4(taxable * gstPct / 100));
        return { total, discount: d, other, taxable, gst_percentage: gstPct, gst, grand: Math.ceil(taxable + gst + other), error };
    }

    // ---------- dates (Indian time, or the app's time zone) ----------

    function dayIn(date, tz) {
        try {
            return new Intl.DateTimeFormat('en-CA', { timeZone: tz || cfg.timezone || 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit' }).format(date);
        } catch (_) {
            const d = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
            return d.toISOString().slice(0, 10);
        }
    }

    function addDays(ymd, n) {
        const d = new Date(ymd + 'T12:00:00Z');
        d.setUTCDate(d.getUTCDate() + n);
        return d.toISOString().slice(0, 10);
    }

    // ---------- barcode / QR lookup ----------

    function lastSegment(raw) {
        let code = String(raw || '').trim();
        for (const m of ['/fc/', '/sc/']) {
            if (code.includes(m)) { code = code.split(m)[1].split('/')[0]; break; }
        }
        if (/^https?:\/\//i.test(code)) {
            const parts = code.split('?')[0].split('#')[0].split('/');
            code = parts[parts.length - 1];
        }
        return code.split('?')[0].split('#')[0].trim();
    }

    /**
     * What a scanned / typed code is: {success, product, size_sets[{..., colors[{key, item, ...}]}]}
     * or {success: false, message}. Prices are not applied here (the order screen prices for the chosen shop).
     */
    async function findVariationByBarcode(rawCode) {
        const code = lastSegment(rawCode);
        if (!code) return { success: false, message: 'Empty code.' };
        const pick = map => map[code] || map[code.toUpperCase()] || map[code.toLowerCase()];

        const blocked = pick(await getBlockedCodes());
        if (blocked) return { success: false, message: blocked };

        const barcodes = await getBarcodeMap();
        const mapped = pick(barcodes);

        // Sample-set (fair) and sample-product tags: the payload built by the office (same as the online scan)
        if (mapped && mapped.payload_key) {
            const payload = (await getScanPayloads())[mapped.payload_key];
            if (payload && payload.size_sets && payload.size_sets.length) {
                const copy = JSON.parse(JSON.stringify(payload));
                if (mapped.size_set_id) {
                    copy.size_sets.sort((a, b) => (b.size_set_id == mapped.size_set_id) - (a.size_set_id == mapped.size_set_id));
                }
                return Object.assign(copy, { success: true, scanned_size_set_id: mapped.size_set_id || null, scanned_color_id: mapped.color_id || null });
            }
        }

        const catalog = await getCatalog();
        if (!catalog.length) return { success: false, message: 'No products on this phone yet. Tap Sync when you have internet.' };

        let productId = null, sizeSetId = null, colorId = null;
        const exact = catalog.find(it => it.barcode && it.barcode.toUpperCase() === code.toUpperCase());
        if (exact) {
            productId = exact.product_id; sizeSetId = exact.size_set_id; colorId = exact.color_id;
        } else if (mapped) {
            productId = mapped.product_id; sizeSetId = mapped.size_set_id || null; colorId = mapped.color_id || null;
        } else {
            let m = code.match(/^D(\d+)S(\d+)C(\d+)/i);
            if (m) { productId = +m[1]; sizeSetId = +m[2]; colorId = +m[3]; }
            if (!productId && (m = code.match(/^(?:FAIR|SMP|SAMPLE)-(\d+)-(\d+)/i))) { productId = +m[1]; sizeSetId = +m[2]; }
            if (!productId) {
                const clean = code.replace(/^[#dD]/, '').toLowerCase();
                const byDesign = catalog.find(it => it.design_number && (it.design_number.toLowerCase() === code.toLowerCase() || it.design_number.toLowerCase() === clean));
                if (byDesign) { productId = byDesign.product_id; sizeSetId = byDesign.size_set_id; }
            }
        }
        if (!productId) return { success: false, message: 'No product found for code "' + code + '" on this phone. If it is new, tap Sync when you have internet.' };

        const items = catalog.filter(it => it.product_id == productId);
        if (!items.length) return { success: false, message: 'This product has no stock on this phone (code "' + code + '").' };

        const first = items[0];
        const series = first.series_name || '';
        const garment = first.name_of_garment ? String(first.name_of_garment).trim() : '';
        const sizeSets = new Map();
        for (const it of items) {
            if (!sizeSets.has(it.size_set_id)) {
                sizeSets.set(it.size_set_id, {
                    size_set_id: it.size_set_id, size_set_name: it.size_set_name || ('Size Set ' + it.size_set_id),
                    product_id: it.product_id, brand_id: it.brand_id, mrp: it.mrp || 0, image: it.image_url || null, colors: []
                });
            }
            const ss = sizeSets.get(it.size_set_id);
            if (!ss.image && it.image_url) ss.image = it.image_url;
            ss.colors.push({
                id: it.color_id, name: it.color_name || ('Color ' + it.color_id), key: it.key, item: it,
                available_boxes: it.available_boxes || 0, is_advance_sample: !!it.is_advance_sample,
                pcs_per_box: it.pcs_per_box || 1, image: it.image_url || null
            });
        }
        const list = [...sizeSets.values()];
        if (sizeSetId) list.sort((a, b) => (b.size_set_id == sizeSetId) - (a.size_set_id == sizeSetId));
        const hero = (items.find(it => it.image_url) || {}).image_url || null;
        return {
            success: true,
            product: {
                id: first.product_id, design_number: first.design_number || 'N/A',
                name: (series && garment && series !== garment) ? series + ' ' + garment : (series || garment || ('Design #' + first.design_number)),
                series_name: series, brand_name: first.brand_name || '', image: hero
            },
            scanned_size_set_id: sizeSetId, scanned_color_id: colorId, size_sets: list
        };
    }

    // ---------- orders on the phone ----------

    function newLocalId() {
        const rnd = (window.crypto && crypto.randomUUID) ? crypto.randomUUID().replace(/-/g, '')
            : (Date.now().toString(36) + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2));
        return 'off2_' + rnd.slice(0, 40);
    }

    /** Orders of the logged-in agent, newest first. */
    async function getOrders() {
        const me = agentId();
        return (await getAll('offline_orders')).filter(o => String(o.agent_id) === me)
            .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }

    async function getOrder(localId) {
        const o = await getOne('offline_orders', localId);
        return o && String(o.agent_id) === agentId() ? o : null;
    }

    /** Orders on this phone that belong to another agent login (or to the old app version, not given to anyone yet). */
    async function countOtherAgentsOrders() {
        const me = agentId();
        return (await getAll('offline_orders')).filter(o => (o.agent_id == null || String(o.agent_id) !== me) && o.status !== 'synced').length;
    }

    /**
     * Orders saved by the app before orders were tied to an agent: given at Sync to the logged-in agent only when
     * the order's shop is one of that agent's shops (a master agent orders for every shop and vendor).
     */
    async function claimUnownedOrders(shopIds, isMaster) {
        const me = agentId();
        if (!me) return 0;
        const mine = new Set((shopIds || []).map(String));
        const db = await openDB();
        const tx = db.transaction('offline_orders', 'readwrite');
        const store = tx.objectStore('offline_orders');
        const all = await req(store.getAll());
        let n = 0;
        all.forEach(o => {
            const forMe = isMaster || ((o.party_type || 'customer') === 'customer' && mine.has(String(o.shop_id)));
            if (o.agent_id == null && forMe) {
                o.agent_id = me;
                if (o.status === 'pending_sync') o.status = 'waiting';
                if (o.sent_unconfirmed === undefined) o.sent_unconfirmed = false;
                store.put(o);
                n++;
            }
        });
        await done(tx);
        return n;
    }

    /** Waiting = not yet answered by the office; refused = the office said no (reason in last_error). */
    function isWaiting(o) { return o && (o.status === 'waiting' || o.status === 'refused' || o.status === 'pending_sync'); }

    /** An order may be changed unless it was sent and the office's answer never came back (it may already be saved). */
    function isEditable(o) { return isWaiting(o) && !o.sent_unconfirmed; }

    /**
     * New order, or changes to one on the phone. Changes are refused when the order has left the phone meanwhile
     * (uploaded or discarded: saving would bring it back) or is being sent right now (the office may already have it).
     */
    async function saveOrder(order) {
        const now = new Date().toISOString();
        const o = Object.assign({}, order, {
            agent_id: agentId(),
            status: 'waiting',
            last_error: null,
            sent_unconfirmed: false,
            updated_at: now
        });
        const isNew = !o.local_id;
        if (isNew) o.local_id = newLocalId();
        if (!o.created_at) o.created_at = now;
        const db = await openDB();
        const tx = db.transaction('offline_orders', 'readwrite');
        const store = tx.objectStore('offline_orders');
        if (!isNew) {
            // checked and written in one transaction, so a Sync running in another tab cannot slip in between
            const cur = await req(store.get(o.local_id));
            if (!cur || String(cur.agent_id) !== agentId()) {
                throw new Error('This order is no longer on this phone (it was uploaded or discarded), so the changes were not saved.');
            }
            if (!isEditable(cur)) {
                throw new Error('This order is being sent to the office right now. Wait until Sync has finished, then try again.');
            }
        }
        store.put(o);
        await done(tx);
        return o;
    }

    /** Marks a waiting order as being sent, read fresh in one transaction; null when it was discarded or is no longer waiting. */
    async function markSending(localId) {
        const db = await openDB();
        const tx = db.transaction('offline_orders', 'readwrite');
        const store = tx.objectStore('offline_orders');
        const o = await req(store.get(localId));
        let sending = null;
        if (o && String(o.agent_id) === agentId() && isWaiting(o)) {
            o.sent_unconfirmed = true;
            o.attempts = (o.attempts || 0) + 1;
            o.last_attempt_at = new Date().toISOString();
            store.put(o);
            sending = o;
        }
        await done(tx);
        return sending;
    }

    /** Changes an order on the phone in one transaction; an order discarded meanwhile stays discarded. */
    async function updateOrder(localId, change) {
        const db = await openDB();
        const tx = db.transaction('offline_orders', 'readwrite');
        const store = tx.objectStore('offline_orders');
        const o = await req(store.get(localId));
        if (o && String(o.agent_id) === agentId()) {
            change(o);
            store.put(o);
        }
        await done(tx);
        return o || null;
    }

    async function deleteOrder(localId) {
        const o = await getOne('offline_orders', localId);
        if (!o || String(o.agent_id) !== agentId()) return false;
        const db = await openDB();
        const tx = db.transaction('offline_orders', 'readwrite');
        tx.objectStore('offline_orders').delete(localId);
        await done(tx);
        return true;
    }

    /** Boxes this agent's other orders on the phone already take, per item "product_colour_size". */
    async function reservedBoxes(exceptLocalId) {
        const out = {};
        (await getOrders()).filter(o => isWaiting(o) && o.local_id !== exceptLocalId).forEach(o => {
            (o.variations || []).forEach(v => {
                const k = v.product_id + '_' + v.color_id + '_' + v.size_set_id;
                out[k] = (out[k] || 0) + (parseInt(v.qty) || 0);
            });
        });
        return out;
    }

    async function waitingCount() {
        return (await getOrders()).filter(isWaiting).length;
    }

    // ---------- talking to the office ----------

    async function http(url, options) {
        let resp;
        try {
            resp = await fetch(url, Object.assign({ credentials: 'same-origin', cache: 'no-store' }, options || {}));
        } catch (e) {
            const err = new Error('No connection to the office. Check the internet and tap Sync again. Your orders are safe on this phone.');
            err.kind = 'network';
            throw err;
        }
        let body = null;
        const text = await resp.text();
        try { body = text ? JSON.parse(text) : null; } catch (_) { body = null; }
        if (!resp.ok || body === null) {
            const err = new Error(
                resp.status === 401 || resp.redirected ? 'You are logged out. Log in again, then tap Sync. Your orders are safe on this phone.'
                : resp.status === 419 ? 'Your login session expired. Reload the page (log in again if asked), then tap Sync. Your orders are safe on this phone.'
                : resp.status === 403 ? 'Your agent account is not active. Ask the office. Your orders are safe on this phone.'
                : 'The office server had a problem (' + (resp.status || 'no answer') + '). Try Sync again in a moment. Your orders are safe on this phone.'
            );
            err.kind = 'server';
            err.status = resp.status;
            throw err;
        }
        return body;
    }

    const jsonHeaders = token => ({ 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token });

    /** What the office needs for one order (prices are only a hint: the office decides). */
    function uploadPayload(o) {
        const lines = (o.variations || []).map(v => ({
            product_id: v.product_id, color_id: v.color_id, size_set_id: v.size_set_id, qty: v.qty,
            pcs_per_box: v.pcs_per_box, unit_price: v.unit_price
        }));
        return {
            local_id: o.local_id, agent_id: o.agent_id, party_type: o.party_type || 'customer', shop_id: o.shop_id,
            is_sample_set: o.is_sample_set ? 1 : 0, variations: lines, line_count: lines.length,
            discount_amount: o.discount_amount || 0, other_charges: o.other_charges || 0, remark: o.remark || '',
            booking_station: o.booking_station || '', transport: o.transport || '', sales_man_id: o.sales_man_id || null,
            expected_dispatch_date: o.expected_dispatch_date || null, order_date: o.order_date || null, booked_at: o.booked_at || o.created_at || null
        };
    }

    /** One sync at a time on this phone (two tabs or two taps would otherwise upload the same order together). */
    let syncRunning = false;
    async function withLock(fn) {
        const busy = () => Object.assign(new Error('A sync is already running (maybe in another tab). Wait for it to finish.'), { kind: 'busy' });
        if (syncRunning) throw busy();
        syncRunning = true;
        try {
            if (navigator.locks && navigator.locks.request) {
                // released by the browser even if the tab is closed mid-sync
                return await navigator.locks.request('snapkid-offline-sync', { ifAvailable: true }, async lock => {
                    if (!lock) throw busy();
                    return fn();
                });
            }
            // older browsers: a time-limited marker shared by the tabs
            const held = parseInt(localStorage.getItem(LOCK_KEY) || '0', 10);
            if (held && Date.now() - held < 5 * 60 * 1000) throw busy();
            localStorage.setItem(LOCK_KEY, String(Date.now()));
            try { return await fn(); } finally { localStorage.removeItem(LOCK_KEY); }
        } finally {
            syncRunning = false;
        }
    }

    /**
     * Sync: 1) upload waiting orders, 2) replace all catalogue data with a fresh download, 3) refresh photos.
     * onStep({phase, pct, text}). Returns a report; report.error is set when the sync stopped early
     * (then the phone's data is left as it was and nothing is lost).
     */
    async function syncAll(onStep) {
        try {
            return await syncLocked(onStep);
        } catch (e) {
            // a Sync already running (another tab): nothing was done, the last Sync's report stays as it is
            return { saved: [], already: [], refused: [], retry: [], unanswered: 0, catalog: null, photos: null,
                error: e.message || String(e), error_kind: e.kind || 'other', at: new Date().toISOString() };
        }
    }

    function syncLocked(onStep) {
        return withLock(async () => {
            const step = (phase, pct, text) => { if (onStep) onStep({ phase, pct: Math.max(0, Math.min(100, Math.round(pct))), text }); };
            const report = { saved: [], already: [], refused: [], retry: [], unanswered: 0, catalog: null, photos: null, error: null, at: new Date().toISOString() };
            try {
                step('check', 2, 'Checking your login…');
                const session = await http(cfg.urls.session, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (String(session.agent_id) !== agentId()) {
                    throw Object.assign(new Error('This browser is now logged in as ' + (session.agent_name || 'another agent') + '. Reload the page.'), { kind: 'agent' });
                }
                const token = session.csrf_token;

                // orders from the old app version (not tied to an agent) whose shop is this agent's
                await claimUnownedOrders(session.shop_ids || [], !!session.is_master);
                // older app versions marked uploaded orders "synced" and kept them: they are done
                for (const o of (await getOrders()).filter(o => o.status === 'synced')) await deleteOrder(o.local_id);

                const queue = (await getOrders()).filter(isWaiting).reverse().map(o => o.local_id); // oldest first
                for (let i = 0; i < queue.length; i++) {
                    // read fresh: an order discarded (or changed) since Sync started is never sent in its old form
                    // from here until the answer arrives the office may already have it: no editing until it is answered
                    const o = await markSending(queue[i]);
                    if (!o) continue;
                    step('upload', 5 + 35 * i / queue.length, 'Uploading order ' + (i + 1) + ' of ' + queue.length + ' (' + (o.shop_name || 'shop') + ')…');
                    let body;
                    try {
                        body = await http(cfg.urls.sync, { method: 'POST', headers: jsonHeaders(token), body: JSON.stringify({ orders: [uploadPayload(o)] }) });
                    } catch (e) {
                        report.unanswered = queue.length - i;
                        throw e;
                    }
                    const r = (body.results || []).find(x => x.local_id === o.local_id);
                    if (!r) {
                        report.unanswered = queue.length - i;
                        throw Object.assign(new Error('The office did not answer for one order. Tap Sync again; it will not be saved twice.'), { kind: 'server' });
                    }
                    if (r.status === 'saved' || r.status === 'already') {
                        await deleteOrder(o.local_id);
                        report[r.status].push({ local_id: o.local_id, order_id: r.order_id, order_no: r.order_no, shop_name: r.shop_name || o.shop_name, grand_total: r.grand_total, total_qty: r.total_qty });
                    } else if (r.status === 'retry') {
                        // the office could not say yet (it may still be saving it): kept as sent, asked again at the next Sync
                        await updateOrder(o.local_id, x => { x.last_error = r.message || null; });
                        report.retry.push({ local_id: o.local_id, shop_name: o.shop_name, message: r.message });
                    } else {
                        const message = r.message || 'Refused by the office.';
                        await updateOrder(o.local_id, x => {
                            x.status = 'refused';
                            x.last_error = message;
                            x.sent_unconfirmed = false;
                            x.refused_at = new Date().toISOString();
                        });
                        report.refused.push({ local_id: o.local_id, shop_name: o.shop_name, message });
                    }
                }

                step('download', 42, 'Downloading shops, products, stock and prices…');
                const data = await http(cfg.urls.catalog, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!data.success) throw Object.assign(new Error(data.message || 'The office could not prepare the data.'), { kind: 'server' });
                if (data.agent && String(data.agent.id) !== agentId()) throw Object.assign(new Error('The download was for another agent login. Reload the page.'), { kind: 'agent' });

                step('save', 55, 'Replacing the data on this phone…');
                await replaceCatalog(data);
                await dropStoredApiCopies();
                report.catalog = { shops: (data.shops || []).length, products: (data.products || []).length };

                try { if (navigator.storage && navigator.storage.persist) await navigator.storage.persist(); } catch (_) {}
                step('photos', 60, 'Checking product photos…');
                report.photos = await refreshImages(data.image_urls || [], (n, total) => step('photos', 60 + 40 * n / Math.max(total, 1), 'Downloading new photos (' + n + ' of ' + total + ')…'));
                await keepOfflinePages(true);
                step('done', 100, 'Done');
            } catch (e) {
                report.error = e.message || String(e);
                report.error_kind = e.kind || 'other';
            }
            try { await putMetaLog(report); } catch (_) {}
            return report;
        });
    }

    async function putMetaLog(report) {
        const db = await openDB();
        const tx = db.transaction('meta', 'readwrite');
        tx.objectStore('meta').put({ key: 'last_sync_' + agentId(), agent_id: agentId(), report });
        await done(tx);
    }

    async function lastSyncReport() {
        const row = await getOne('meta', 'last_sync_' + agentId());
        return row ? row.report : null;
    }

    /**
     * Ask the service worker to save fresh copies of the two offline pages (hub + Take Order) for the logged-in agent,
     * so both open without internet even if the agent never opened Take Order online. Runs after login (logout
     * removes the copies), after every Sync (force) and otherwise at most every 6 hours. True when both were saved.
     */
    async function keepOfflinePages(force) {
        if (!('serviceWorker' in navigator) || !navigator.onLine || !agentId()) return false;
        try {
            const last = JSON.parse(localStorage.getItem(PAGES_KEY) || 'null');
            if (!force && last && String(last.agent) === agentId() && Date.now() - last.at < 6 * 3600 * 1000) return false;
        } catch (_) {}
        try {
            const reg = await navigator.serviceWorker.getRegistration(cfg.base + 'agent/');
            const worker = reg && reg.active;
            if (!worker) return false; // first visit: the worker saves both pages itself while installing
            const stored = await new Promise(resolve => {
                const ch = new MessageChannel();
                const timer = setTimeout(() => resolve(0), 20000);
                ch.port1.onmessage = e => { clearTimeout(timer); resolve((e.data && e.data.stored) || 0); };
                worker.postMessage({ type: 'sk-keep-offline-pages' }, [ch.port2]);
            });
            if (stored >= 2) {
                try { localStorage.setItem(PAGES_KEY, JSON.stringify({ agent: agentId(), at: Date.now() })); } catch (_) {}
                return true;
            }
        } catch (_) {}
        return false;
    }

    /**
     * On logout: remove the agent's shops, products and saved page copies from the phone.
     * Orders not uploaded yet stay (only this agent sees them, after logging in again).
     */
    async function clearForLogout() {
        try {
            const db = await openDB();
            const tx = db.transaction(['catalog', 'shops', 'meta'], 'readwrite');
            ['catalog', 'shops', 'meta'].forEach(n => tx.objectStore(n).clear());
            await done(tx);
        } catch (_) {}
        try { localStorage.removeItem(PAGES_KEY); } catch (_) {}
        if ('caches' in window) {
            try {
                for (const n of await caches.keys()) {
                    if (n.indexOf(PAGE_CACHE_PREFIX) === 0 || /^snapkid-agent-v\d+$/.test(n)) await caches.delete(n);
                }
            } catch (_) {}
        }
    }

    /**
     * "Show pricing info" (sales agent setting in admin): the newest word wins - the page the server just sent
     * (pageTime = server time in ms when it made the page) or the last catalogue download.
     */
    async function resolveSeePrice(pageValue, pageTime) {
        pageValue = !!pageValue;
        let meta = null;
        try { meta = await getMeta(); } catch (_) {}
        if (!meta) return pageValue;
        const metaTime = meta.server_time ? Date.parse(meta.server_time) : 0;
        if (typeof meta.see_price !== 'undefined' && metaTime > pageTime) return meta.see_price === true;
        if (meta.see_price !== pageValue) {
            try {
                meta.see_price = pageValue;
                meta.server_time = new Date(pageTime).toISOString();
                await putMeta(meta);
            } catch (_) {}
        }
        return pageValue;
    }

    async function putMeta(row) {
        const db = await openDB();
        const tx = db.transaction('meta', 'readwrite');
        tx.objectStore('meta').put(row);
        await done(tx);
    }

    return {
        configure, openDB,
        getMeta, getShops, getCatalog, getBarcodeMap, getScanPayloads, getBlockedCodes, getFairDiscounts,
        resolveSeePrice, findVariationByBarcode, lastSegment, getImageBlobUrl,
        priceAfter, orderTotals, dayIn, addDays,
        newLocalId, getOrders, getOrder, saveOrder, deleteOrder, isWaiting, isEditable, reservedBoxes, waitingCount,
        countOtherAgentsOrders, syncAll, lastSyncReport, clearForLogout, keepOfflinePages, refreshImages
    };
})();

window.SnapKidOfflineDB = SnapKidOfflineDB;
