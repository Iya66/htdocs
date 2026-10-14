<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
?>

<style>
/* SaaS Navigation Tabs */
.saas-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border-color, #E2E8F0);
    padding-bottom: 12px;
}

.saas-tab-btn {
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    background: transparent;
    color: var(--text-secondary, #64748B);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.saas-tab-btn:hover {
    background: #F1F5F9;
    color: var(--text-main, #0F172A);
}

.saas-tab-btn.active {
    background: var(--primary, #6366F1);
    color: #FFFFFF;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    animation: tabFadeIn 0.3s ease;
}

@keyframes tabFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
    align-items: start;
}

@media (max-width: 992px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
}

.settings-card { 
    background: var(--bg-card, #FFFFFF); 
    border: 1px solid var(--border-color, #E2E8F0); 
    border-radius: var(--border-radius, 16px); 
    padding: 30px; 
    box-shadow: var(--shadow-sm, 0 4px 20px rgba(0,0,0,0.04)); 
    margin-bottom: 24px;
    position: relative;
}

.settings-card h3 { 
    font-size: 17px; 
    color: var(--text-main, #0F172A); 
    margin-bottom: 6px; 
    display: flex; 
    align-items: center; 
    gap: 10px;
    font-weight: 700;
}
.settings-card h3 i { color: var(--primary, #6366F1); }

.card-subtitle {
    color: var(--text-secondary, #64748B);
    font-size: 13px;
    margin-bottom: 20px;
}

.form-group { margin-bottom: 18px; }
.form-group label { 
    display: block; 
    margin-bottom: 8px; 
    color: var(--text-main, #1E293B); 
    font-weight: 600; 
    font-size: 13px; 
    display: flex; 
    align-items: center; 
    justify-content: space-between;
}

.form-control { 
    width: 100%; 
    padding: 12px 16px; 
    border: 1px solid var(--border-color, #CBD5E1); 
    border-radius: var(--btn-radius, 10px); 
    font-size: 14px; 
    background: var(--bg-main, #F8FAFC); 
    color: var(--text-main, #0F172A); 
    transition: all var(--transition, 0.25s ease); 
    box-sizing: border-box;
}
.form-control:focus { 
    outline: none; 
    border-color: var(--primary, #6366F1); 
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    background: #FFFFFF;
}

/* Map Preview Card & Controls */
.map-preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 10px;
}

.badge-live-map {
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

.dot-live-radar {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: liveMapPulse 2s infinite;
}

@keyframes liveMapPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.map-location-tag {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    color: #334155;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.map-preview {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid var(--border-color, #E2E8F0);
    background: var(--bg-main, #F8FAFC);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 290px;
    height: 290px;
    width: 100%;
    position: relative;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
}

.map-preview iframe {
    width: 100%;
    height: 100%;
    min-height: 290px;
    border: 0;
    display: block;
}

.map-input-mode-pills {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.map-mode-btn {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid #CBD5E1;
    background: #F8FAFC;
    color: #475569;
    transition: all 0.2s ease;
}

.map-mode-btn.active {
    background: var(--primary, #6366F1);
    color: #FFFFFF;
    border-color: var(--primary, #6366F1);
}

.btn-open-maps {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--primary, #6366F1);
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.2);
    text-decoration: none;
    transition: all 0.2s ease;
}
.btn-open-maps:hover {
    background: var(--primary, #6366F1);
    color: #FFFFFF;
}

.btn-secondary-outline {
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
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
.btn-secondary-outline:hover {
    background: #E2E8F0;
    color: #0F172A;
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
        <h1 class="page-title"><i class="fa-solid fa-address-book" style="color: var(--primary);"></i> Contact &amp; Map Management</h1>
        <p class="page-subtitle">Configure contact information, business hours, and Google Maps live embed.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="map" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
            <i class="fa-solid fa-map-location-dot"></i> Dedicated Map Controller
        </a>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-<?= $type ?>">
        <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> 
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Navigation Tabs -->
<div class="saas-tabs">
    <button type="button" class="saas-tab-btn active" id="tabBtnMap" onclick="switchContactTab('map')">
        <i class="fa-solid fa-map-location-dot"></i> Google Map Page Editor &amp; Preview
    </button>
    <button type="button" class="saas-tab-btn" id="tabBtnContact" onclick="switchContactTab('contact')">
        <i class="fa-solid fa-address-card"></i> Contact Details &amp; Social Profiles
    </button>
</div>

<!-- TAB 1: GOOGLE MAP PAGE EDITOR & LIVE PREVIEW -->
<div class="tab-pane active" id="tabPaneMap">
    <div class="contact-grid">
        <!-- MAP EDITOR FORM -->
        <div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <!-- Preserve other contact fields on save -->
                <input type="hidden" name="contact_email" value="<?= htmlspecialchars($contact_email) ?>">
                <input type="hidden" name="contact_phone" value="<?= htmlspecialchars($contact_phone) ?>">
                <input type="hidden" name="contact_whatsapp" value="<?= htmlspecialchars($contact_whatsapp) ?>">
                <input type="hidden" name="contact_hours" value="<?= htmlspecialchars($contact_hours) ?>">
                <input type="hidden" name="social_facebook" value="<?= htmlspecialchars($social_facebook) ?>">
                <input type="hidden" name="social_instagram" value="<?= htmlspecialchars($social_instagram) ?>">
                <input type="hidden" name="social_twitter" value="<?= htmlspecialchars($social_twitter) ?>">
                <input type="hidden" name="social_linkedin" value="<?= htmlspecialchars($social_linkedin) ?>">

                <div class="settings-card">
                    <h3><i class="fa-solid fa-pen-to-square"></i> Map Page Location Editor</h3>
                    <p class="card-subtitle">
                        Edit the office address or coordinates shown on your client Contact Us page (<code>/contact</code>).
                    </p>

                    <div class="map-input-mode-pills">
                        <button type="button" class="map-mode-btn active" id="btnModeAddress">By Address / City</button>
                        <button type="button" class="map-mode-btn" id="btnModeIframe">By Direct Embed Code</button>
                    </div>
                    
                    <div class="form-group" id="groupAddressInput">
                        <label>
                            <span><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> Company Name / Office Address / Landmark</span>
                            <span style="font-size: 11px; color: var(--text-secondary); font-weight: normal;">Live preview updates instantly</span>
                        </label>
                        <input type="text" name="map_location" id="mapLocationInput" class="form-control" value="<?= htmlspecialchars($current_location) ?>" placeholder="e.g. Cloud Aspire, Puducherry" required>
                        <span style="font-size: 12px; color: var(--text-secondary); margin-top: 5px; display: block;">
                            Enter your company name (e.g. <strong>Cloud Aspire</strong>) or full address. Google Maps will show your business pin directly.
                        </span>
                    </div>

                    <div class="form-group" id="groupIframeInput" style="display: none;">
                        <label><i class="fa-solid fa-code" style="color: var(--primary);"></i> Raw Google Maps Embed Code</label>
                        <textarea id="rawEmbedCodeInput" class="form-control" rows="4" placeholder='<iframe src="https://www.google.com/maps/embed?..." ...></iframe>'><?= htmlspecialchars($contact_map_iframe) ?></textarea>
                        <span style="font-size: 12px; color: var(--text-secondary); margin-top: 5px; display: block;">
                            Paste custom iframe copied from Google Maps &gt; Share &gt; Embed a map.
                        </span>
                    </div>

                    <div class="form-group" style="margin-top: 16px;">
                        <label>
                            <span><i class="fa-solid fa-key" style="color: var(--primary);"></i> Google Maps JavaScript API Key (Optional)</span>
                            <span style="font-size: 11px; color: var(--text-secondary); font-weight: normal;">Google Cloud Console</span>
                        </label>
                        <input type="text" name="google_maps_api_key" id="googleMapsApiKeyInput" class="form-control" value="<?= htmlspecialchars($google_maps_api_key ?? '') ?>" placeholder="AIzaSy...">
                        <span style="font-size: 12px; color: var(--text-secondary); margin-top: 5px; display: block;">
                            Enter your Google Cloud API key if you have one. Otherwise, the map runs in high-performance unblocked Google Maps API mode automatically.
                        </span>
                    </div>

                    <!-- Hidden input submitted with the form -->
                    <textarea name="contact_map_iframe" id="mapIframeInput" style="display: none;"><?= htmlspecialchars($contact_map_iframe) ?></textarea>

                    <div style="display: flex; gap: 10px; margin-top: 20px; align-items: center; flex-wrap: wrap;">
                        <button type="submit" name="save_contact" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save Map Location
                        </button>
                        <button type="button" class="btn-secondary-outline" onclick="resetToPuducherry()">
                            <i class="fa-solid fa-rotate-left"></i> Reset to Puducherry HQ
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- MAP LIVE PREVIEW PANEL -->
        <div>
            <div style="position: sticky; top: 20px;">
                <div class="settings-card">
                    <div class="map-preview-header">
                        <div>
                            <h3 style="margin-bottom: 2px;"><i class="fa-solid fa-map"></i> Live Map Preview</h3>
                            <span style="color: var(--text-secondary); font-size: 12px;">Real-time front-end client rendering</span>
                        </div>
                        <span class="badge-live-map">
                            <span class="dot-live-radar"></span> Active Preview
                        </span>
                    </div>

                    <div class="map-location-tag">
                        <i class="fa-solid fa-location-crosshairs" style="color: var(--primary);"></i>
                        <span id="mapPreviewLocationLabel" style="font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                            <?= htmlspecialchars($current_location) ?>
                        </span>
                        <a id="btnTestGoogleMaps" href="https://maps.google.com/?q=<?= urlencode($current_location) ?>" target="_blank" rel="noopener noreferrer" class="btn-open-maps" style="margin-left: auto; flex-shrink: 0;">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in Maps
                        </a>
                    </div>

                    <div class="map-preview" id="mapPreviewBox" style="position: relative; padding: 0;">
                        <!-- DOM-Based Interactive Map (Zero Iframe Blocking, 100% Guaranteed Display) -->
                        <div id="adminMapDOM" style="width: 100%; height: 100%; min-height: 290px; border-radius: 14px; z-index: 1;"></div>
                    </div>

                    <div style="margin-top: 14px; display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--text-secondary);">
                        <span><i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Responsive 100% width container</span>
                        <span>Direct Google Maps Embed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: CONTACT DETAILS & SOCIAL PROFILES -->
<div class="tab-pane" id="tabPaneContact">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="contact_map_iframe" value="<?= htmlspecialchars($contact_map_iframe) ?>">

        <div class="contact-grid">
            <div class="settings-card">
                <h3><i class="fa-solid fa-pen-to-square"></i> General Contact Details</h3>
                <p class="card-subtitle">Update email, phone, and operating hours shown across the website.</p>

                <div class="form-group">
                    <label><i class="fa-solid fa-envelope" style="color: var(--primary);"></i> Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($contact_email) ?>" placeholder="e.g. support@yourdomain.com" required>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-phone" style="color: var(--primary);"></i> Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($contact_phone) ?>" placeholder="e.g. +91 80988 30937" required>
                </div>

                <div class="form-group">
                    <label><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> WhatsApp Number</label>
                    <input type="text" name="contact_whatsapp" class="form-control" value="<?= htmlspecialchars($contact_whatsapp) ?>" placeholder="e.g. 8098830937" required>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-clock" style="color: var(--primary);"></i> Business Hours</label>
                    <input type="text" name="contact_hours" class="form-control" value="<?= htmlspecialchars($contact_hours) ?>" placeholder="e.g. Mon - Sat: 9:00 AM - 7:00 PM" required>
                </div>

                <button type="submit" name="save_contact" class="btn btn-primary" style="margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Contact Details
                </button>
            </div>

            <div class="settings-card">
                <h3><i class="fa-solid fa-share-nodes"></i> Social Media Links</h3>
                <p class="card-subtitle">Connect your active social profiles displayed on the footer &amp; contact page.</p>

                <div class="form-group">
                    <label><i class="fa-brands fa-facebook" style="color: #1877F2;"></i> Facebook URL</label>
                    <input type="url" name="social_facebook" class="form-control" value="<?= htmlspecialchars($social_facebook) ?>" placeholder="https://facebook.com/yourpage">
                </div>

                <div class="form-group">
                    <label><i class="fa-brands fa-instagram" style="color: #E1306C;"></i> Instagram URL</label>
                    <input type="url" name="social_instagram" class="form-control" value="<?= htmlspecialchars($social_instagram) ?>" placeholder="https://instagram.com/yourprofile">
                </div>

                <div class="form-group">
                    <label><i class="fa-brands fa-x-twitter" style="color: #0F172A;"></i> Twitter / X URL</label>
                    <input type="url" name="social_twitter" class="form-control" value="<?= htmlspecialchars($social_twitter) ?>" placeholder="https://twitter.com/yourhandle">
                </div>

                <div class="form-group">
                    <label><i class="fa-brands fa-linkedin" style="color: #0A66C2;"></i> LinkedIn URL</label>
                    <input type="url" name="social_linkedin" class="form-control" value="<?= htmlspecialchars($social_linkedin) ?>" placeholder="https://linkedin.com/in/yourprofile">
                </div>

                <button type="submit" name="save_contact" class="btn btn-primary" style="margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Social Media Links
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Leaflet DOM Map Engine Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
let adminMap = null;
let adminMarker = null;

function switchContactTab(tab) {
    document.getElementById('tabBtnMap').classList.toggle('active', tab === 'map');
    document.getElementById('tabBtnContact').classList.toggle('active', tab === 'contact');
    document.getElementById('tabPaneMap').classList.toggle('active', tab === 'map');
    document.getElementById('tabPaneContact').classList.toggle('active', tab === 'contact');
    if (history.replaceState) {
        history.replaceState(null, null, '#' + tab);
    }
    if (tab === 'map' && adminMap) {
        setTimeout(() => { adminMap.invalidateSize(); }, 150);
    }
}

function resetToPuducherry() {
    const locInput = document.getElementById('mapLocationInput');
    locInput.value = 'Cloud Aspire, Puducherry';
    locInput.dispatchEvent(new Event('input'));
}

(function() {
    // 1. Initialize DOM Map
    const defaultLat = <?= (float)($settings['contact_map_lat'] ?? 11.93605) ?>;
    const defaultLng = <?= (float)($settings['contact_map_lng'] ?? 79.80387) ?>;
    const currentLoc = <?= json_encode($current_location) ?>;

    adminMap = L.map('adminMapDOM', {
        center: [defaultLat, defaultLng],
        zoom: 14,
        zoomControl: true,
        scrollWheelZoom: false,
        attributionControl: false // Completely remove Leaflet watermark
    });

    // Real Google Maps Tile Layers
    const googleRoadmap = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: ''
    }).addTo(adminMap);

    const googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: ''
    });

    L.control.layers({ "Map": googleRoadmap, "Satellite": googleSatellite }, null, { position: 'topright' }).addTo(adminMap);

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

    adminMarker = L.marker([defaultLat, defaultLng], { icon: googlePinIcon }).addTo(adminMap);
    adminMarker.bindPopup(`
        <div style="font-family: inherit; padding: 2px;">
            <strong style="font-size: 13px; color: #0F172A; display: block;">${currentLoc}</strong>
            <span style="font-size: 11px; color: #EA4335; font-weight: 700;">Google Maps Live Pin</span>
        </div>
    `).openPopup();

    setTimeout(() => { adminMap.invalidateSize(); }, 300);

    // Check URL hash for direct tab navigation
    if (window.location.hash === '#contact') {
        switchContactTab('contact');
    } else {
        switchContactTab('map');
    }

    const locationInput = document.getElementById('mapLocationInput');
    const rawEmbedInput = document.getElementById('rawEmbedCodeInput');
    const iframeTextarea = document.getElementById('mapIframeInput');
    const locationLabel = document.getElementById('mapPreviewLocationLabel');
    const testMapsBtn = document.getElementById('btnTestGoogleMaps');

    const btnModeAddress = document.getElementById('btnModeAddress');
    const btnModeIframe = document.getElementById('btnModeIframe');
    const groupAddress = document.getElementById('groupAddressInput');
    const groupIframe = document.getElementById('groupIframeInput');

    btnModeAddress.addEventListener('click', function() {
        btnModeAddress.classList.add('active');
        btnModeIframe.classList.remove('active');
        groupAddress.style.display = 'block';
        groupIframe.style.display = 'none';
    });

    btnModeIframe.addEventListener('click', function() {
        btnModeIframe.classList.add('active');
        btnModeAddress.classList.remove('active');
        groupAddress.style.display = 'none';
        groupIframe.style.display = 'block';
    });

    function updateFromAddress(location) {
        const trimmed = location.trim();
        if (!trimmed) return;

        locationLabel.textContent = trimmed;
        testMapsBtn.href = `https://maps.google.com/?q=${encodeURIComponent(trimmed)}`;

        // Update popup immediately
        if (adminMarker) {
            adminMarker.bindPopup(`
                <div style="font-family: inherit; padding: 2px;">
                    <strong style="font-size: 13px; color: #0F172A; display: block;">${trimmed}</strong>
                    <span style="font-size: 11px; color: #64748B;">Live Company Pin</span>
                </div>
            `).openPopup();
        }

        // Geocode to pan map if address/city/company recognized
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(trimmed)}&limit=1`)
        .then(r => r.json())
        .then(data => {
            if (data && data.length > 0 && adminMap && adminMarker) {
                const newLat = parseFloat(data[0].lat);
                const newLng = parseFloat(data[0].lon);
                adminMap.flyTo([newLat, newLng], 15);
                adminMarker.setLatLng([newLat, newLng]);
            }
        })
        .catch(() => {});
    }

    function updateFromRawEmbed(code) {
        const trimmed = code.trim();
        if (trimmed && trimmed.includes('<iframe')) {
            iframeTextarea.value = trimmed;
            locationLabel.textContent = 'Custom Google Maps Embed Code';
        }
    }

    let debounceTimer;
    locationInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updateFromAddress(e.target.value);
        }, 350);
    });

    rawEmbedInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updateFromRawEmbed(e.target.value);
        }, 350);
    });
})();
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>
