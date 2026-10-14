<?php
/**
 * Admin Map Controller View
 * Modern SaaS UI & UX for controlling Google Maps locations, zooms, views, and live preview.
 */
require_once __DIR__ . '/../../../admin/includes/header.php';
?>

<style>
.map-ctrl-layout {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 28px;
    align-items: start;
}

@media (max-width: 1024px) {
    .map-ctrl-layout {
        grid-template-columns: 1fr;
    }
}

.saas-card {
    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--border-color, #E2E8F0);
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    margin-bottom: 24px;
    position: relative;
}

.saas-card h3 {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-main, #0F172A);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.saas-card h3 i {
    color: var(--primary, #6366F1);
}

.saas-card-subtitle {
    color: var(--text-secondary, #64748B);
    font-size: 13px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main, #1E293B);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color, #CBD5E1);
    border-radius: 10px;
    font-size: 14px;
    background: var(--bg-main, #F8FAFC);
    color: var(--text-main, #0F172A);
    transition: all 0.25s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary, #6366F1);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    background: #FFFFFF;
}

/* Map Mode Pills */
.map-type-selector {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 6px;
}

.map-type-btn {
    padding: 10px 8px;
    text-align: center;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border-color, #E2E8F0);
    background: #F8FAFC;
    color: #475569;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.map-type-btn i {
    font-size: 14px;
}

.map-type-btn:hover {
    background: #F1F5F9;
    color: #0F172A;
}

.map-type-btn.active {
    background: var(--primary, #6366F1);
    color: #FFFFFF;
    border-color: var(--primary, #6366F1);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}

.map-type-btn.active i {
    color: #FFFFFF;
}

/* Zoom Slider */
.zoom-slider-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #F8FAFC;
    padding: 14px 18px;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
}

.zoom-slider-wrap input[type="range"] {
    flex: 1;
    cursor: pointer;
    accent-color: var(--primary, #6366F1);
}

.zoom-value-badge {
    background: var(--primary, #6366F1);
    color: #FFFFFF;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    min-width: 50px;
    text-align: center;
}

/* Preview Box (Compact SaaS height) */
.map-preview-container {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid var(--border-color, #E2E8F0);
    background: #F8FAFC;
    height: 290px;
    min-height: 290px;
    width: 100%;
    position: relative;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
}

.map-preview-container iframe {
    width: 100%;
    height: 100%;
    min-height: 290px;
    border: 0;
    display: block;
}

.badge-live-radar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(16, 185, 129, 0.1);
    color: #10B981;
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-size: 12px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 100px;
}

.dot-radar {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: radarPulse 2s infinite;
}

@keyframes radarPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.btn-group-saas {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 10px;
}

.btn-secondary-saas {
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}
.btn-secondary-saas:hover {
    background: #E2E8F0;
    color: #0F172A;
}

.info-chip {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 13px;
    color: #334155;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Completely Remove Leaflet & Map Watermarks */
.leaflet-control-attribution,
.leaflet-control-attribution a,
.leaflet-bottom.leaflet-right .leaflet-control-attribution {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
    height: 0 !important;
    width: 0 !important;
    overflow: hidden !important;
}
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-map-location-dot" style="color: var(--primary);"></i> Map Controller</h1>
        <p class="page-subtitle">Configure Google Maps coordinates, zoom levels, view modes, and live embed display across your website.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <span class="badge-live-radar">
            <span class="dot-radar"></span> Contact Page Active
        </span>
        <a href="<?= $base_path ?>../contact" target="_blank" rel="noopener noreferrer" class="btn-secondary-saas" style="padding: 6px 14px; font-size: 12px;">
            <i class="fa-solid fa-external-link-alt"></i> View User Page
        </a>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-<?= $type ?>">
        <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> 
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="map-ctrl-layout">
    <!-- LEFT: CONTROLLER CONTROLS -->
    <div>
        <form method="POST" id="mapControllerForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="map_type" id="hiddenMapType" value="<?= htmlspecialchars($mapData['map_type'] ?? 'm') ?>">

            <div class="saas-card">
                <h3><i class="fa-solid fa-location-crosshairs"></i> Location &amp; Coordinates</h3>
                <p class="saas-card-subtitle">Set your primary headquarters address, landmark, or GPS coordinates.</p>

                <div class="form-group">
                    <label>
                        <span><i class="fa-solid fa-building" style="color: var(--primary);"></i> Company Name / Office Address / Landmark</span>
                        <span style="font-size: 11px; color: var(--text-secondary); font-weight: normal;">Updates preview instantly</span>
                    </label>
                    <input type="text" name="map_location" id="inputLocation" class="form-control" 
                           value="<?= htmlspecialchars($mapData['location'] ?? 'Cloud Aspire') ?>" 
                           placeholder="e.g. Cloud Aspire, Puducherry" required>
                    <span style="font-size: 12px; color: var(--text-secondary); margin-top: 5px; display: block;">
                        Type your company name (e.g. <strong>Cloud Aspire</strong>) or address. Google Maps will show your business pin directly.
                    </span>
                </div>

                <div class="form-group">
                    <label>
                        <span>Interactive Zoom Level</span>
                        <span id="zoomLevelDescription" style="font-size: 11px; color: var(--primary); font-weight: 600;">14x (Street / Area)</span>
                    </label>
                    <div class="zoom-slider-wrap">
                        <span style="font-size: 12px; color: #64748B;">1x</span>
                        <input type="range" name="map_zoom" id="inputZoom" min="1" max="20" value="<?= (int)($mapData['zoom'] ?? 14) ?>">
                        <span style="font-size: 12px; color: #64748B;">20x</span>
                        <div class="zoom-value-badge" id="zoomBadge"><?= (int)($mapData['zoom'] ?? 14) ?>x</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Map Display View Mode</label>
                    <div class="map-type-selector">
                        <button type="button" class="map-type-btn <?= ($mapData['map_type'] ?? 'm') === 'm' ? 'active' : '' ?>" data-type="m">
                            <i class="fa-solid fa-map"></i> Roadmap
                        </button>
                        <button type="button" class="map-type-btn <?= ($mapData['map_type'] ?? '') === 'k' ? 'active' : '' ?>" data-type="k">
                            <i class="fa-solid fa-satellite"></i> Satellite
                        </button>
                        <button type="button" class="map-type-btn <?= ($mapData['map_type'] ?? '') === 'h' ? 'active' : '' ?>" data-type="h">
                            <i class="fa-solid fa-layer-group"></i> Hybrid
                        </button>
                        <button type="button" class="map-type-btn <?= ($mapData['map_type'] ?? '') === 'p' ? 'active' : '' ?>" data-type="p">
                            <i class="fa-solid fa-mountain"></i> Terrain
                        </button>
                    </div>
                </div>

                <!-- Custom Embed Code Accordion -->
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
                    <details>
                        <summary style="font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-code" style="color: var(--primary);"></i> Advanced: Custom &lt;iframe&gt; Embed Code
                        </summary>
                        <div style="margin-top: 14px;">
                            <textarea name="map_raw_iframe" id="inputRawIframe" class="form-control" rows="3" 
                                      placeholder='Paste custom <iframe src="https://maps.google.com/maps?..."></iframe>'></textarea>
                            <span style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; display: block;">
                                Leave blank to automatically generate embed from the address above.
                            </span>
                        </div>
                    </details>
                </div>

                <div class="btn-group-saas" style="margin-top: 24px;">
                    <button type="submit" name="save_map_settings" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Map Configuration
                    </button>
                    <button type="submit" name="reset_map_default" class="btn-secondary-saas" 
                            onclick="return confirm('Reset map to default Puducherry headquarters?');">
                        <i class="fa-solid fa-rotate-left"></i> Reset to Default
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- RIGHT: LIVE SAAS PREVIEW PANEL -->
    <div>
        <div style="position: sticky; top: 20px;">
            <div class="saas-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="margin: 0;"><i class="fa-solid fa-eye"></i> Live Map Preview</h3>
                    <span style="font-size: 12px; color: var(--text-secondary);">Compact SaaS View</span>
                </div>

                <div class="info-chip">
                    <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                        <i class="fa-solid fa-map-pin" style="color: var(--primary); flex-shrink: 0;"></i>
                        <span id="previewLocationName" style="font-weight: 600; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                            <?= htmlspecialchars($mapData['location'] ?? 'No 53 Auvai Nagar, Puducherry') ?>
                        </span>
                    </div>
                </div>

                <div class="map-preview-container" id="liveMapBox" style="position: relative; padding: 0;">
                    <!-- DOM-Based Interactive Map (Zero Iframe Blocking, 100% Guaranteed Display) -->
                    <div id="adminDedicatedMapDOM" style="width: 100%; height: 100%; min-height: 290px; border-radius: 14px; z-index: 1;"></div>
                </div>

                <div style="margin-top: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; gap: 8px;">
                        <a id="linkGoogleMaps" href="<?= htmlspecialchars($mapData['maps_url'] ?? 'https://maps.google.com/?q=Cloud+Aspire') ?>" target="_blank" rel="noopener noreferrer" class="btn-secondary-saas" style="padding: 6px 14px; font-size: 12px;">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in Maps
                        </a>
                        <a id="linkDirections" href="<?= htmlspecialchars($mapData['directions_url'] ?? 'https://www.google.com/maps/dir/?api=1&destination=Cloud+Aspire') ?>" target="_blank" rel="noopener noreferrer" class="btn-secondary-saas" style="padding: 6px 14px; font-size: 12px;">
                            <i class="fa-solid fa-diamond-turn-right"></i> Directions
                        </a>
                    </div>
                    <span style="font-size: 12px; color: #10B981; font-weight: 600;">
                        <i class="fa-solid fa-circle-check"></i> DOM Interactive Map Active
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet DOM Map Engine Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
(function() {
    const inputLocation = document.getElementById('inputLocation');
    const inputZoom = document.getElementById('inputZoom');
    const zoomBadge = document.getElementById('zoomBadge');
    const zoomDesc = document.getElementById('zoomLevelDescription');
    const previewLocationName = document.getElementById('previewLocationName');
    const linkGoogleMaps = document.getElementById('linkGoogleMaps');
    const linkDirections = document.getElementById('linkDirections');
    const typeButtons = document.querySelectorAll('.map-type-btn');

    // Initialize Dedicated Leaflet DOM Map
    const defaultLat = 11.93605;
    const defaultLng = 79.80387;
    const initialLocation = <?= json_encode($mapData['location'] ?? 'Cloud Aspire') ?>;

    const dedicatedMap = L.map('adminDedicatedMapDOM', {
        center: [defaultLat, defaultLng],
        zoom: parseInt(inputZoom.value) || 14,
        zoomControl: true,
        scrollWheelZoom: false,
        attributionControl: false // Completely remove Leaflet watermark
    });

    function getGoogleLayer(type) {
        let lyrCode = 'm';
        if (type === 'k') lyrCode = 's'; // satellite
        if (type === 'h') lyrCode = 'y'; // hybrid
        if (type === 'p') lyrCode = 'p'; // terrain
        return L.tileLayer(`https://{s}.google.com/vt/lyrs=${lyrCode}&x={x}&y={y}&z={z}`, {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: ''
        });
    }

    let currentTileLayer = getGoogleLayer('m').addTo(dedicatedMap);

    // Iconic Google Maps Red Pin
    const googlePinIcon = L.divIcon({
        className: 'custom-google-pin',
        html: `<div style="width: 34px; height: 44px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.35)); cursor: pointer;">
            <svg viewBox="0 0 24 24" width="34" height="44" fill="#EA4335">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                <circle cx="12" cy="9" r="3" fill="#FFFFFF"/>
            </svg>
        </div>`,
        iconSize: [34, 44],
        iconAnchor: [17, 44],
        popupAnchor: [0, -42]
    });

    let mapMarker = L.marker([defaultLat, defaultLng], { icon: googlePinIcon }).addTo(dedicatedMap);
    mapMarker.bindPopup(`
        <div style="font-family: inherit; padding: 2px;">
            <strong style="font-size: 13px; color: #0F172A; display: block;">${initialLocation}</strong>
            <span style="font-size: 11px; color: #EA4335; font-weight: 700;">Google Maps Live Pin</span>
        </div>
    `).openPopup();

    setTimeout(() => { dedicatedMap.invalidateSize(); }, 300);

    // Zoom description helper
    function getZoomDescription(z) {
        if (z <= 4) return `${z}x (Continental / World)`;
        if (z <= 8) return `${z}x (Country / State)`;
        if (z <= 12) return `${z}x (Metro / City)`;
        if (z <= 16) return `${z}x (Neighborhood / Street)`;
        return `${z}x (Building / Precise)`;
    }

    // Map Type switchers (Roadmap, Satellite, Hybrid, Terrain - All Official Google Maps!)
    typeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            typeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const type = this.getAttribute('data-type');
            
            dedicatedMap.removeLayer(currentTileLayer);
            currentTileLayer = getGoogleLayer(type).addTo(dedicatedMap);
        });
    });

    // Zoom slider event
    inputZoom.addEventListener('input', function() {
        zoomBadge.textContent = `${this.value}x`;
        zoomDesc.textContent = getZoomDescription(parseInt(this.value));
        dedicatedMap.setZoom(parseInt(this.value));
    });

    // Address input typing debounce
    let locDebounce;
    inputLocation.addEventListener('input', function() {
        clearTimeout(locDebounce);
        locDebounce = setTimeout(() => {
            const loc = this.value.trim();
            if (!loc) return;
            previewLocationName.textContent = loc;
            linkGoogleMaps.href = `https://maps.google.com/?q=${encodeURIComponent(loc)}`;
            linkDirections.href = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(loc)}`;
            
            mapMarker.bindPopup(`
                <div style="font-family: inherit; padding: 2px;">
                    <strong style="font-size: 13px; color: #0F172A; display: block;">${loc}</strong>
                    <span style="font-size: 11px; color: #64748B;">Live Company Pin</span>
                </div>
            `).openPopup();

            // Geocode
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(loc)}&limit=1`)
            .then(r => r.json())
            .then(data => {
                if (data && data.length > 0) {
                    const newLat = parseFloat(data[0].lat);
                    const newLng = parseFloat(data[0].lon);
                    dedicatedMap.flyTo([newLat, newLng], parseInt(inputZoom.value) || 14);
                    mapMarker.setLatLng([newLat, newLng]);
                }
            })
            .catch(() => {});
        }, 350);
    });
})();
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>
