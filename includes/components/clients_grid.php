<?php
/**
 * Reusable Component: 3D Auto-Flip Clients Grid (Connected to Database)
 * 
 * Sources client names from:
 *  1. $projects table (client_name and industry)
 *  2. $settings['clients_list'] (Admin-configurable comma-separated list)
 *  3. Dynamic icon mapping
 */

$clients_heading = !empty($settings['clients_heading']) ? $settings['clients_heading'] : "Our Client's";

// 1. Fetch from projects table if not already passed
if (empty($projects) && isset($conn)) {
    try {
        $projects = $conn->query("SELECT client_name, industry FROM projects WHERE client_name IS NOT NULL AND client_name != '' LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $projects = [];
    }
}

$client_cards = [];

// From projects
if (!empty($projects)) {
    foreach ($projects as $proj) {
        if (!empty($proj['client_name'])) {
            $client_cards[] = [
                'name' => $proj['client_name'],
                'icon' => 'fa-building-columns',
                'category' => !empty($proj['industry']) ? $proj['industry'] : 'Enterprise'
            ];
        }
    }
}

// From settings['clients_list']
$configured_clients_str = $settings['clients_list'] ?? '';
if (!empty($configured_clients_str)) {
    $raw_names = array_map('trim', explode(',', $configured_clients_str));
    $icons = ['fa-spa', 'fa-hotel', 'fa-building', 'fa-palette', 'fa-utensils', 'fa-ship', 'fa-heart-pulse', 'fa-plane-departure', 'fa-cake-candles', 'fa-trowel-bricks', 'fa-bowl-rice', 'fa-graduation-cap', 'fa-briefcase', 'fa-tree-city', 'fa-couch', 'fa-cloud'];
    $categories = ['Wellness Clinic', 'Hospitality', 'Construction', 'Art & Culture', 'Luxury Dining', 'Tourism', 'Health Retreat', 'Global Travel', 'Retail Sweets', 'Real Estate', 'Restaurant', 'Education', 'Corporate Enterprise', 'Properties', 'Interior Design', 'Software Solutions'];

    foreach ($raw_names as $idx => $cname) {
        if (!empty($cname)) {
            $client_cards[] = [
                'name' => $cname,
                'icon' => $icons[$idx % count($icons)],
                'category' => $categories[$idx % count($categories)]
            ];
        }
    }
}

// Slice to maximum 16 cards
$client_cards = array_slice($client_cards, 0, 16);
?>

<style>
.cdm-clients-section-comp {
    padding: 80px 0;
    background-color: #e9eeff;
}

.cdm-clients-grid-comp {
    max-width: 1240px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 18px;
}

.cdm-client-card-comp {
    height: 110px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(3, 34, 143, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    padding: 12px;
    text-align: center;
}

.cdm-client-card-comp.flip {
    transform: rotateY(180deg);
}

.cdm-client-face-comp {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 12px;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 8px;
}

.cdm-client-face-comp.back {
    transform: rotateY(180deg);
    background: #ffffff;
}

.cdm-client-logo-icon-comp {
    font-size: 24px;
    color: var(--primary-color, #03228f);
    margin-bottom: 6px;
}

.cdm-client-name-comp {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    line-height: 1.25;
}

@media (max-width: 1100px) {
    .cdm-clients-grid-comp {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 600px) {
    .cdm-clients-section-comp {
        padding: 45px 16px;
    }
    .cdm-clients-grid-comp {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding: 0;
    }
    .cdm-client-card-comp {
        height: 95px;
        padding: 8px;
        min-width: 0;
        overflow: hidden;
    }
    .cdm-client-name-comp {
        font-size: 10.5px;
        word-break: break-word;
        overflow-wrap: break-word;
    }
}
</style>

<section class="cdm-clients-section-comp">
    <div style="text-align: center; margin-bottom: 50px;">
        <h2 style="font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 800; color: #03228f; margin-bottom: 12px;">
            <?= htmlspecialchars($clients_heading) ?>
        </h2>
        <div style="width: 60px; height: 4px; background: #fb9a27; margin: 0 auto; border-radius: 2px;"></div>
    </div>

    <div class="cdm-clients-grid-comp">
        <?php foreach ($client_cards as $idx => $client): ?>
        <div class="cdm-client-card-comp" id="clientCard_<?= $idx ?>">
            <div class="cdm-client-face-comp front">
                <i class="fas <?= htmlspecialchars($client['icon']) ?> cdm-client-logo-icon-comp"></i>
                <div class="cdm-client-name-comp"><?= htmlspecialchars($client['name']) ?></div>
            </div>
            <div class="cdm-client-face-comp back">
                <i class="fas <?= htmlspecialchars($client['icon']) ?> cdm-client-logo-icon-comp" style="color: #fb9a27;"></i>
                <div class="cdm-client-name-comp" style="color: #03228f;"><?= htmlspecialchars($client['category']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
if (typeof flipClientCardsInitialized === 'undefined') {
    var flipClientCardsInitialized = true;
    setInterval(function() {
        var cards = document.querySelectorAll('.cdm-client-card-comp');
        cards.forEach(function(card) {
            card.classList.toggle('flip');
        });
    }, 5000);
}
</script>
