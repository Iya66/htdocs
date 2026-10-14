<?php 
/**
 * Contact Us View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$active_services = $active_services ?? [];
$selected_service = $selected_service ?? '';
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : ($contact_email ?? 'cloudaspire2025@gmail.com');
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : ($contact_phone ?? '8098830937');
$contact_whatsapp = !empty($settings['contact_whatsapp']) ? $settings['contact_whatsapp'] : ($contact_whatsapp ?? '8098830937');
$contact_hours = !empty($settings['contact_hours']) ? $settings['contact_hours'] : ($contact_hours ?? 'Mon - Sat: 9:00 AM - 7:00 PM');
$contact_address = !empty($settings['contact_address']) ? $settings['contact_address'] : 'No. 53, Auvai Nagar, Puducherry, 605008';
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$msg_status = $msg_status ?? null;
$msg_text = $msg_text ?? '';

// Fallback: If active_services is not passed, fetch directly from DB
if (empty($active_services) && isset($conn)) {
    try {
        $active_services = $conn->query("SELECT id, service_name, slug, price FROM services WHERE status = 'active' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $active_services = [];
    }
}

include __DIR__ . '/../../../includes/header.php'; 
?>

<!-- CHERRIDM INNER PAGE HERO -->
<style>
    .cdm-page-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?auto=format&fit=crop&w=1600&q=80') center/cover;
        padding: 90px 20px 80px;
        text-align: center;
        color: #ffffff;
        position: relative;
    }
    .cdm-page-hero h1 {
        font-size: clamp(2.4rem, 4.5vw, 3.6rem);
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    .cdm-page-hero h1 span.stroke {
        color: #fb9a27;
    }
    .cdm-page-hero p {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
        max-width: 680px;
        margin: 0 auto 24px;
        line-height: 1.6;
    }
    .cdm-breadcrumb-capsule {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        padding: 7px 22px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
    }
    .cdm-breadcrumb-capsule a {
        color: #ffffff;
        transition: color 0.2s ease;
        text-decoration: none;
    }
    .cdm-breadcrumb-capsule a:hover {
        color: #fb9a27;
    }
    .cdm-breadcrumb-capsule span.sep {
        color: rgba(255, 255, 255, 0.5);
    }
    .cdm-breadcrumb-capsule span.active {
        color: #fb9a27;
    }

    /* Layout Wrapper */
    .cdm-contact-wrapper {
        display: grid;
        grid-template-columns: 1.25fr 0.85fr;
        gap: 36px;
        align-items: start;
    }
    .cdm-contact-form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 44px 40px;
        box-shadow: 0 8px 30px rgba(3, 34, 143, 0.06);
        position: relative;
        overflow: hidden;
    }
    .cdm-contact-form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #03228f, #fb9a27, #03228f);
    }
    .cdm-contact-form-card h3 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #03228f;
        margin: 0 0 8px 0;
    }
    .cdm-contact-form-card p.subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 28px;
        line-height: 1.6;
    }

    /* Input Controls */
    .cdm-input-label {
        display: block;
        font-size: 0.88rem;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 8px;
    }
    .cdm-form-input {
        width: 100%;
        padding: 13px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #0f172a;
        background: #f8faff;
        transition: all 0.25s ease;
        outline: none;
        box-sizing: border-box;
    }
    .cdm-form-input:focus {
        background: #ffffff;
        border-color: #03228f;
        box-shadow: 0 0 0 3px rgba(3, 34, 143, 0.12);
    }

    /* Info Sidebar */
    .cdm-contact-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .cdm-info-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 36px 30px;
        box-shadow: 0 8px 30px rgba(3, 34, 143, 0.06);
        position: relative;
        overflow: hidden;
    }
    .cdm-info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #03228f;
    }
    .cdm-info-card h3 {
        font-size: 1.4rem;
        font-weight: 800;
        color: #03228f;
        margin: 0 0 20px 0;
    }
    .cdm-info-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: transform 0.2s ease;
    }
    .cdm-info-item:last-of-type {
        border-bottom: none;
    }
    .cdm-info-item:hover {
        transform: translateX(4px);
    }
    .cdm-info-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(3, 34, 143, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #03228f;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .cdm-info-item:hover .cdm-info-icon {
        background: #fb9a27;
        color: #ffffff;
        transform: scale(1.08);
    }
    .cdm-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        display: inline-block;
        margin-right: 6px;
        animation: cdmPulse 2s infinite ease-in-out;
    }
    @keyframes cdmPulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
        70% { transform: scale(1.2); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .cdm-whatsapp-banner {
        margin-top: 20px;
        padding: 15px;
        border-radius: 12px;
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.3);
        transition: all 0.3s ease;
    }
    .cdm-whatsapp-banner:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(37, 211, 102, 0.45);
        color: #ffffff;
    }

    .cdm-guarantee-strip {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 24px;
    }
    .cdm-guarantee-chip {
        background: #f8faff;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.84rem;
        font-weight: 600;
        color: #475569;
    }
    .cdm-guarantee-chip i {
        color: #fb9a27;
        font-size: 1rem;
    }

    /* Map Box */
    .cdm-map-card {
        margin-top: 50px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 30px rgba(3, 34, 143, 0.06);
        background: #ffffff;
        position: relative;
    }
    .cdm-map-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #03228f, #fb9a27, #03228f);
        z-index: 2;
    }

    @media (max-width: 992px) {
        .cdm-contact-wrapper {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .cdm-contact-form-card {
            padding: 30px 20px;
        }
        .cdm-guarantee-strip {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="cdm-page-hero">
    <div class="container">
        <h1>Contact <span class="stroke">Us</span></h1>
        <p>Let's discuss how we can accelerate your digital growth and maximize your marketing ROI</p>
        <div class="cdm-breadcrumb-capsule">
            <a href="<?= $base_url ?>/"><i class="fas fa-home"></i> Home</a>
            <span class="sep">/</span>
            <span class="active">Contact</span>
        </div>
    </div>
</section>

<section style="padding: 70px 0; background-color: #f8faff;">
    <div class="container">
        <div class="cdm-contact-wrapper">
            
            <!-- Left Form Column -->
            <div class="cdm-contact-form-card cdm-reveal-left">
                <h3>Send Us a Message</h3>
                <p class="subtitle">Fill out the form below and our strategy team will respond within 2 hours.</p>

                <?php if ($msg_status === 'success'): ?>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 500;">
                        <i class="fas fa-check-circle" style="color: #10B981; font-size: 1.3rem;"></i>
                        <div><?= $msg_text ?></div>
                    </div>
                <?php elseif ($msg_status === 'error'): ?>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991B1B; padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 500;">
                        <i class="fas fa-exclamation-circle" style="color: #EF4444; font-size: 1.3rem;"></i>
                        <div><?= $msg_text ?></div>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" id="contactForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                        <div>
                            <label class="cdm-input-label">Full Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="name" class="cdm-form-input" placeholder="e.g. John Doe" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                        </div>
                        <div>
                            <label class="cdm-input-label">Email Address <span style="color: #ef4444;">*</span></label>
                            <input type="email" name="email" class="cdm-form-input" placeholder="e.g. john@company.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 18px;">
                        <div>
                            <label class="cdm-input-label">Phone Number <span style="color: #ef4444;">*</span></label>
                            <input type="tel" name="phone" class="cdm-form-input" placeholder="+91 80988 30937" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                        </div>
                        <div>
                            <label class="cdm-input-label">Select Service <span style="color: #ef4444;">*</span></label>
                            <select name="service" class="cdm-form-input" required>
                                <option value="" disabled <?= empty($_POST['service']) && empty($_GET['service']) ? 'selected' : '' ?>>Select a Service</option>
                                <?php
                                $current_sel = $_POST['service'] ?? ($_GET['service'] ?? '');
                                foreach ($active_services as $svc):
                                    $sname = $svc['service_name'] ?? '';
                                    $is_sel = (strcasecmp($sname, $current_sel) === 0);
                                ?>
                                    <option value="<?= htmlspecialchars($sname) ?>" <?= $is_sel ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sname) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="margin-top: 18px; margin-bottom: 24px;">
                        <label class="cdm-input-label">Your Message / Project Goals <span style="color: #ef4444;">*</span></label>
                        <textarea name="message" class="cdm-form-input" rows="4" placeholder="Tell us about your business goals, target audience, or current digital challenges..." required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                    </div>

                    <button type="submit" class="cdm-btn-quote" style="width: 100%; justify-content: center; padding: 15px;">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Strategic Inquiry</span>
                    </button>
                </form>

                <div class="cdm-guarantee-strip">
                    <div class="cdm-guarantee-chip"><i class="fas fa-shield-halved"></i> 100% Confidential</div>
                    <div class="cdm-guarantee-chip"><i class="fas fa-bolt"></i> 2-Hour Response SLA</div>
                </div>
            </div>

            <!-- Right Info Column -->
            <div class="cdm-contact-sidebar">
                <?php
                $whatsapp_clean = preg_replace('/[^0-9]/', '', $contact_whatsapp);
                if (strlen($whatsapp_clean) === 10) {
                    $whatsapp_clean = '91' . $whatsapp_clean;
                }
                ?>
                <div class="cdm-info-card cdm-reveal-right">
                    <h3>Direct Connect</h3>
                    
                    <div class="cdm-info-item">
                        <div class="cdm-info-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <strong style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Direct Phone</strong>
                            <a href="tel:<?= htmlspecialchars($contact_phone) ?>" style="font-weight: 700; color: #03228f; font-size: 1.05rem; text-decoration: none;">
                                <?= htmlspecialchars($contact_phone) ?>
                            </a>
                        </div>
                    </div>

                    <div class="cdm-info-item">
                        <div class="cdm-info-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <strong style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Email Inquiries</strong>
                            <a href="mailto:<?= htmlspecialchars($contact_email) ?>" style="font-weight: 600; color: #03228f; text-decoration: none;">
                                <?= htmlspecialchars($contact_email) ?>
                            </a>
                        </div>
                    </div>

                    <div class="cdm-info-item">
                        <div class="cdm-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <strong style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Headquarters</strong>
                            <span style="font-size: 0.92rem; color: #0f172a; font-weight: 500; line-height: 1.4;">
                                <?= htmlspecialchars($contact_address) ?>
                            </span>
                        </div>
                    </div>

                    <div class="cdm-info-item">
                        <div class="cdm-info-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <strong style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">Operating Hours</strong>
                            <span style="font-size: 0.92rem; color: #0f172a; font-weight: 600;">
                                <span class="cdm-pulse-dot"></span> <?= htmlspecialchars($contact_hours) ?>
                            </span>
                        </div>
                    </div>

                    <a href="https://wa.me/<?= $whatsapp_clean ?>" target="_blank" rel="noopener noreferrer" class="cdm-whatsapp-banner">
                        <i class="fab fa-whatsapp" style="font-size: 1.3rem;"></i>
                        <span>Chat Instantly on WhatsApp</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Google Map Interactive Preview Section -->
        <?php
        $display_location = !empty($settings['contact_map_location']) ? $settings['contact_map_location'] : $contact_address;
        $directions_url = 'https://maps.google.com/?q=' . urlencode($display_location);
        ?>
        <div class="cdm-map-card">
            <div style="padding: 16px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: gap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-location-dot" style="color: #fb9a27; font-size: 1.1rem;"></i>
                    <strong style="color: #03228f; font-size: 0.95rem;"><?= htmlspecialchars($site_name) ?> Headquarters</strong>
                    <span style="color: #64748b; font-size: 0.85rem;">&bull; <?= htmlspecialchars($display_location) ?></span>
                </div>
                <a href="<?= $directions_url ?>" target="_blank" rel="noopener noreferrer" style="color: #03228f; font-weight: 700; font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <span>Open in Google Maps</span>
                    <i class="fas fa-arrow-up-right-from-square"></i>
                </a>
            </div>

            <!-- DOM Map Container -->
            <div id="contactMapDOM" style="width: 100%; height: 380px;"></div>
        </div>

    </div>
</section>

<!-- MAP SCRIPTS -->
<?php 
$gmaps_api_param = !empty($settings['google_maps_api_key']) ? '&key=' . urlencode($settings['google_maps_api_key']) : '';
?>
<script src="https://maps.googleapis.com/maps/api/js?v=weekly<?= $gmaps_api_param ?>&callback=initGoogleMapsAPI&loading=async" async defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<style>
.leaflet-control-attribution, .leaflet-bottom.leaflet-right { display: none !important; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
let googleMapsLoaded = false;
const mapLat = <?= (float)($settings['contact_map_lat'] ?? 11.93605) ?>;
const mapLng = <?= (float)($settings['contact_map_lng'] ?? 79.80387) ?>;
const companyTitle = <?= json_encode($display_location) ?>;
const directionsUrl = <?= json_encode($directions_url) ?>;

window.initGoogleMapsAPI = function() {
    const mapElement = document.getElementById('contactMapDOM');
    if (!mapElement) return;

    googleMapsLoaded = true;
    mapElement.innerHTML = '';

    const centerCoords = { lat: mapLat, lng: mapLng };
    const googleMapsInstance = new google.maps.Map(mapElement, {
        zoom: 15,
        center: centerCoords,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
        zoomControl: true
    });

    const googleMarker = new google.maps.Marker({
        position: centerCoords,
        map: googleMapsInstance,
        title: companyTitle,
        animation: google.maps.Animation.DROP
    });

    const googleInfoWindow = new google.maps.InfoWindow({
        content: `
            <div style="font-family: inherit; padding: 4px; min-width: 210px;">
                <strong style="font-size: 14px; color: #03228f; display: block; margin-bottom: 2px;">${companyTitle}</strong>
                <a href="${directionsUrl}" target="_blank" rel="noopener noreferrer" style="color: #fb9a27; font-weight: 700; font-size: 12px; text-decoration: none; margin-top: 6px; display: inline-flex; align-items: center; gap: 4px;">
                    Get Directions &rarr;
                </a>
            </div>
        `
    });

    googleMarker.addListener('click', function() {
        googleInfoWindow.open(googleMapsInstance, googleMarker);
    });
};

window.gm_authFailure = function() {
    fallbackToLeafletMap();
};

setTimeout(function() {
    if (!googleMapsLoaded) {
        fallbackToLeafletMap();
    }
}, 2000);

function fallbackToLeafletMap() {
    const mapElement = document.getElementById('contactMapDOM');
    if (!mapElement || mapElement.querySelector('.leaflet-pane') || googleMapsLoaded) return;

    mapElement.innerHTML = '';
    const leafletMapInstance = L.map('contactMapDOM', {
        center: [mapLat, mapLng],
        zoom: 15,
        zoomControl: true,
        scrollWheelZoom: false,
        attributionControl: false
    });

    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: ''
    }).addTo(leafletMapInstance);

    const pinIcon = L.divIcon({
        className: 'custom-google-pin',
        html: `<div style="width: 32px; height: 42px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3)); cursor: pointer;">
            <svg viewBox="0 0 24 24" width="32" height="42" fill="#03228f">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                <circle cx="12" cy="9" r="3" fill="#fb9a27"/>
            </svg>
        </div>`,
        iconSize: [32, 42],
        iconAnchor: [16, 42]
    });

    const marker = L.marker([mapLat, mapLng], { icon: pinIcon }).addTo(leafletMapInstance);
    marker.bindPopup(`
        <div style="font-family: inherit; padding: 4px;">
            <strong style="font-size: 13px; color: #03228f;">${companyTitle}</strong><br>
            <a href="${directionsUrl}" target="_blank" rel="noopener noreferrer" style="color: #fb9a27; font-weight: 700; font-size: 11px;">Directions &rarr;</a>
        </div>
    `);
}
</script>

<!-- BOTTOM CTA BANNER -->
<section style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); padding: 70px 20px; color: #ffffff; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
            // Let's Build Together //
        </span>
        <h3 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 12px;">Prefer an In-Person Meeting?</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px;">
            Visit our Pondicherry digital strategy office or schedule a high-level video consultation with our founders.
        </p>
        <button type="button" class="cdm-btn-quote" style="display: inline-flex;" onclick="openContactPopup()">
            <span>Schedule Strategic Call</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</section>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
