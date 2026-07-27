<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

$activePage = 'dashboard';
$pageTitle  = 'کورپاڼه - ' . APP_NAME;

$outgoingCount = db()->query('SELECT COUNT(*) c FROM outgoing_letters')->fetch()['c'];
$incomingCount = db()->query('SELECT COUNT(*) c FROM incoming_letters')->fetch()['c'];
$receiptsCount = db()->query('SELECT COUNT(*) c FROM receipts')->fetch()['c'];

$today = date('Y-m-d');
$outgoingToday = db()->prepare('SELECT COUNT(*) c FROM outgoing_letters WHERE DATE(created_at) = :d');
$outgoingToday->execute(['d' => $today]);
$outgoingToday = $outgoingToday->fetch()['c'];

$incomingToday = db()->prepare('SELECT COUNT(*) c FROM incoming_letters WHERE DATE(created_at) = :d');
$incomingToday->execute(['d' => $today]);
$incomingToday = $incomingToday->fetch()['c'];

$receiptsToday = db()->prepare('SELECT COUNT(*) c FROM receipts WHERE DATE(created_at) = :d');
$receiptsToday->execute(['d' => $today]);
$receiptsToday = $receiptsToday->fetch()['c'];

$recentOutgoing = db()->query('SELECT o.id, o.serial_no, o.subject, o.dossier_no, o.letter_date, sent_dep.name AS sent_to_department FROM outgoing_letters o LEFT JOIN departments sent_dep ON sent_dep.id = o.sent_to_dep_id ORDER BY o.id DESC LIMIT 6')->fetchAll();
$recentIncoming = db()->query('SELECT i.id, i.serial_no, i.subject, i.dossier_no, i.letter_date, sent_dep.name AS sent_to_department, origin_dep.name AS origin_department FROM incoming_letters i LEFT JOIN departments sent_dep ON sent_dep.id = i.sent_to_dep_id LEFT JOIN departments origin_dep ON origin_dep.id = i.origin_dep_id ORDER BY i.id DESC LIMIT 6')->fetchAll();
$recentReceipts = db()->query('SELECT r.id, r.serial_no, r.archive, r.name, r.action_no, r.letter_date, sent_dep.name AS sent_to_department, origin_dep.name AS origin_department FROM receipts r LEFT JOIN departments sent_dep ON sent_dep.id = r.sent_to_dep_id LEFT JOIN departments origin_dep ON origin_dep.id = r.origin_dep_id ORDER BY r.id DESC LIMIT 6')->fetchAll();

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
        <h3>ټول رسیدات</h3>
        <div class="stat-value"><?= (int)$receiptsCount ?></div>
    </div>
    <div class="stat-card">
        <h3>نن ورځ صادره</h3>
        <div class="stat-value"><?= (int)$outgoingToday ?></div>
    </div>
    <div class="stat-card accent">
        <h3>نن ورځ وارده</h3>
        <div class="stat-value"><?= (int)$incomingToday ?></div>
    </div>
    <div class="stat-card">
        <h3>نن ورځ رسیدات</h3>
        <div class="stat-value"><?= (int)$receiptsToday ?></div>
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
                    <td><?= e($row['sent_to_department'] ?? '—') ?></td>
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
                    <td><?= e($row['sent_to_department'] ?? '—') ?></td>
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
    <div class="page-header"><h1 style="font-size:1.15rem">وروستي رسیدات</h1>
        <a href="receipts/add.php" class="btn btn-primary btn-sm">+ نوی رسید ثبت کړئ</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>مسلسل نمبر</th><th>مرسل</th><th>مرسل الیه</th><th>تسلیمی</th><th>نیټه وردود</th></tr></thead>
            <tbody>
            <?php if (!$recentReceipts): ?>
                <tr><td colspan="5" class="empty-state">هېڅ ثبت شوی نه دی</td></tr>
            <?php endif; ?>
            <?php foreach ($recentReceipts as $row): ?>
                <tr onclick="location.href='receipts/view.php?id=<?= (int)$row['id'] ?>'" style="cursor:pointer">
                    <td><?= e($row['serial_no']) ?></td>
                    <td><?= e($row['origin_department'] ?? '_') ?></td>
                    <td><?= e($row['sent_to_department'] ?? '—') ?></td>
                    <td><?= e($row['name']) ?></td>
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
            },
            {
                label:'رسیدات',
                data:[
                    <?= (int)$receiptsCount ?>,
                    <?= (int)$receiptsToday ?>
                ],
                borderColor:'#f59e0b',
                backgroundColor:'rgba(245,158,11,.15)',
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
        labels:['صادره','وارده','رسیدات'],
        datasets:[{
            data:[
                <?= (int)$outgoingCount ?>,
                <?= (int)$incomingCount ?>,
                <?= (int)$receiptsCount ?>
            ],
            backgroundColor:[
                '#2563eb',
                '#16a34a',
                '#f59e0b'
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
            'رسیدات',
            'نن صادره',
            'نن وارده',
            'نن رسیدات'
        ],
        datasets:[{
            label:'شمېر',
            data:[
                <?= (int)$outgoingCount ?>,
                <?= (int)$incomingCount ?>,
                <?= (int)$receiptsCount ?>,
                <?= (int)$outgoingToday ?>,
                <?= (int)$incomingToday ?>,
                <?= (int)$receiptsToday ?>
            ],
            backgroundColor:[
                '#2563eb',
                '#16a34a',
                '#f59e0b',
                '#60a5fa',
                '#4ade80',
                '#fbbf24'
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
            'رسیدات',
            'نن صادره',
            'نن وارده',
            'نن رسیدات'
        ],
        datasets:[{
            label:'فعالیت',
            data:[
                <?= (int)$outgoingCount ?>,
                <?= (int)$incomingCount ?>,
                <?= (int)$receiptsCount ?>,
                <?= (int)$outgoingToday ?>,
                <?= (int)$incomingToday ?>,
                <?= (int)$receiptsToday ?>
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