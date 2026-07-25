<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

$activePage = 'dashboard';
$pageTitle  = 'کورپاڼه - ' . APP_NAME;

$outgoingCount = db()->query('SELECT COUNT(*) c FROM outgoing_letters')->fetch()['c'];
$incomingCount = db()->query('SELECT COUNT(*) c FROM incoming_letters')->fetch()['c'];

$today = date('Y-m-d');
$outgoingToday = db()->prepare('SELECT COUNT(*) c FROM outgoing_letters WHERE DATE(created_at) = :d');
$outgoingToday->execute(['d' => $today]);
$outgoingToday = $outgoingToday->fetch()['c'];

$incomingToday = db()->prepare('SELECT COUNT(*) c FROM incoming_letters WHERE DATE(created_at) = :d');
$incomingToday->execute(['d' => $today]);
$incomingToday = $incomingToday->fetch()['c'];

$recentOutgoing = db()->query('SELECT id, serial_no, sent_to, subject,dossier_no, letter_date FROM outgoing_letters ORDER BY id DESC LIMIT 6')->fetchAll();
$recentIncoming = db()->query('SELECT id, serial_no, sent_from, subject,dossier_no, letter_date FROM incoming_letters ORDER BY id DESC LIMIT 6')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>د سیستم کورپاڼه</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>ټول صادره مکتوبونه</h3>
        <div class="stat-value"><?= (int)$outgoingCount ?></div>
    </div>
    <div class="stat-card accent">
        <h3>ټول وارده مکتوبونه</h3>
        <div class="stat-value"><?= (int)$incomingCount ?></div>
    </div>
    <div class="stat-card">
        <h3>نن ورځ صادره</h3>
        <div class="stat-value"><?= (int)$outgoingToday ?></div>
    </div>
    <div class="stat-card accent">
        <h3>نن ورځ وارده</h3>
        <div class="stat-value"><?= (int)$incomingToday ?></div>
    </div>
</div>

<!-- chart view and cards  -->
 <div class="card">
    <div class="page-header">
        <h1 style="font-size:1.15rem">د مکتوبونو تحلیلي راپور</h1>
    </div>

    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
        gap:20px;
    ">

        <div style="height:300px">
            <canvas id="lineChart"></canvas>
        </div>

        <div style="height:300px">
            <canvas id="doughnutChart"></canvas>
        </div>

        <div style="height:300px">
            <canvas id="barChart"></canvas>
        </div>

        <div style="height:300px">
            <canvas id="radarChart"></canvas>
        </div>

    </div>
</div>
 
<div class="card">
    <div class="page-header"><h1 style="font-size:1.15rem">وروستي وارده مکتوبونه</h1>
        <a href="incoming/add.php" class="btn btn-primary btn-sm">+ نوی وارده ثبت کړئ</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>مسلسل نمبر</th><th>مرسله الیه</th><th>موضوع</th><th>دوسیه نمبر</th><th>نیټه وردود</th></tr></thead>
            <tbody>
            <?php if (!$recentIncoming): ?>
                <tr><td colspan="4" class="empty-state">هېڅ ثبت شوی نه دی</td></tr>
            <?php endif; ?>
            <?php foreach ($recentIncoming as $row): ?>
                <tr onclick="location.href='incoming/view.php?id=<?= (int)$row['id'] ?>'" style="cursor:pointer">
                    <td><?= e($row['serial_no']) ?></td>
                    <td><?= e($row['sent_from']) ?></td>
                    <td class="subject-cell"><?= e(mb_strimwidth($row['subject'] ?? '', 0, 60, '…')) ?></td>
                    <td><?= e($row['dossier_no']) ?></td>
                    <td><?= e($row['letter_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="page-header"><h1 style="font-size:1.15rem">وروستي صادره مکتوبونه</h1>
        <a href="outgoing/add.php" class="btn btn-primary btn-sm">+ نوی صادره ثبت کړئ</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>مسلسل نمبر</th><th>مرسل الیه</th><th>موضوع</th><th>دوسیه نمبر</th><th>نیټه وردود</th></tr></thead>
            <tbody>
            <?php if (!$recentOutgoing): ?>
                <tr><td colspan="4" class="empty-state">هېڅ ثبت شوی نه دی</td></tr>
            <?php endif; ?>
            <?php foreach ($recentOutgoing as $row): ?>
                <tr onclick="location.href='outgoing/view.php?id=<?= (int)$row['id'] ?>'" style="cursor:pointer">
                    <td><?= e($row['serial_no']) ?></td>
                    <td><?= e($row['sent_to']) ?></td>
                    <td class="subject-cell"><?= e(mb_strimwidth($row['subject'] ?? '', 0, 60, '…')) ?></td>
                    <td><?= e($row['dossier_no']) ?></td>
                    <td><?= e($row['letter_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const chartAnimation = {
    duration: 1800,
    easing: 'easeOutQuart'
};


// Line Chart
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: ['ټول','نن'],
        datasets: [
            {
                label:'صادره',
                data:[
                    <?= (int)$outgoingCount ?>,
                    <?= (int)$outgoingToday ?>
                ],
                borderColor:'#2563eb',
                backgroundColor:'rgba(37,99,235,.15)',
                fill:true,
                tension:.4
            },
            {
                label:'وارده',
                data:[
                    <?= (int)$incomingCount ?>,
                    <?= (int)$incomingToday ?>
                ],
                borderColor:'#16a34a',
                backgroundColor:'rgba(22,163,74,.15)',
                fill:true,
                tension:.4
            }
        ]
    },
    options:{
        responsive:true,
        animation:chartAnimation,
        plugins:{
            title:{
                display:true,
                text:'د مکتوبونو پرمختګ'
            }
        }
    }
});


// Doughnut Chart
new Chart(document.getElementById('doughnutChart'), {
    type:'doughnut',
    data:{
        labels:['صادره','وارده'],
        datasets:[{
            data:[
                <?= (int)$outgoingCount ?>,
                <?= (int)$incomingCount ?>
            ],
            backgroundColor:[
                '#2563eb',
                '#16a34a'
            ],
            borderWidth:2
        }]
    },
    options:{
        responsive:true,
        animation:chartAnimation,
        plugins:{
            title:{
                display:true,
                text:'ټولیز تقسیم'
            }
        }
    }
});


// Bar Chart
new Chart(document.getElementById('barChart'), {
    type:'bar',
    data:{
        labels:[
            'صادره',
            'وارده',
            'نن صادره',
            'نن وارده'
        ],
        datasets:[{
            label:'شمېر',
            data:[
                <?= (int)$outgoingCount ?>,
                <?= (int)$incomingCount ?>,
                <?= (int)$outgoingToday ?>,
                <?= (int)$incomingToday ?>
            ],
            backgroundColor:[
                '#2563eb',
                '#16a34a',
                '#60a5fa',
                '#4ade80'
            ],
            borderRadius:8
        }]
    },
    options:{
        responsive:true,
        animation:chartAnimation,
        plugins:{
            title:{
                display:true,
                text:'مقایسوي راپور'
            }
        }
    }
});


// Radar Chart
new Chart(document.getElementById('radarChart'), {
    type:'radar',
    data:{
        labels:[
            'صادره',
            'وارده',
            'نن صادره',
            'نن وارده'
        ],
        datasets:[{
            label:'فعالیت',
            data:[
                <?= (int)$outgoingCount ?>,
                <?= (int)$incomingCount ?>,
                <?= (int)$outgoingToday ?>,
                <?= (int)$incomingToday ?>
            ],
            backgroundColor:'rgba(168,85,247,.25)',
            borderColor:'#9333ea',
            pointBackgroundColor:'#9333ea'
        }]
    },
    options:{
        responsive:true,
        animation:chartAnimation,
        plugins:{
            title:{
                display:true,
                text:'د سیستم فعالیت'
            }
        }
    }
});

</script>


<?php require __DIR__ . '/includes/footer.php'; ?>
