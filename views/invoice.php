<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>فاتورة - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      background: #f4f5fa;
      color: #212121;
      padding: 20px;
    }
    .invoice {
      max-width: 420px;
      margin: 0 auto;
      background: #fff;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.1);
      border: 2px solid #ff3b30;
    }
    .inv-header {
      text-align: center;
      border-bottom: 2px dashed #ff3b30;
      padding-bottom: 16px;
      margin-bottom: 16px;
    }
    .inv-header h1 { color: #1f1f2c; font-size: 22px; margin-bottom: 4px; }
    .inv-header .sub { color: #8b8b9a; font-size: 12px; }
    .badge {
      display: inline-block;
      background: #ff3b30;
      color: #15151f;
      padding: 4px 12px;
      border-radius: 16px;
      font-size: 11px;
      font-weight: bold;
      margin-top: 8px;
    }
    .meta { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 16px; color: #3a3a4d; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 13px; }
    th {
      background: #f4f5fa;
      padding: 8px;
      text-align: right;
      border-bottom: 1px solid #ececf2;
    }
    td { padding: 8px; border-bottom: 1px solid #f4f5fa; }
    .total-row {
      font-size: 18px;
      font-weight: bold;
      color: #e8590c;
      text-align: left;
      padding-top: 12px;
      border-top: 2px solid #ff3b30;
    }
    .footer {
      text-align: center;
      font-size: 11px;
      color: #8b8b9a;
      margin-top: 20px;
      padding-top: 12px;
      border-top: 1px dashed #ececf2;
    }
    .actions { max-width: 420px; margin: 20px auto 0; display: flex; gap: 10px; }
    .actions button, .actions a {
      flex: 1;
      padding: 12px;
      border-radius: 10px;
      border: none;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
    }
    .btn-print { background: #ff3b30; color: #15151f; }
    .btn-back { background: #eee; color: #3a3a4d; }
    @media print {
      body { background: #fff; padding: 0; }
      .actions { display: none !important; }
      .invoice { box-shadow: none; border: 1px solid #ccc; }
    }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="invoice" id="invoiceBox">
    <div class="inv-header">
      <h1 id="restName"><?= e(APP_NAME) ?></h1>
      <div class="sub" id="restAddress">أول مدخل المنصورية</div>
      <div class="sub" id="restPhone">☎ 01153431728</div>
      <div class="badge">فاتورتك إلكترونية</div>
    </div>

    <div class="meta">
      <div>
        <div>رقم الطلب: <strong id="orderId">-</strong></div>
        <div>التاريخ: <span id="orderDate">-</span></div>
      </div>
      <div style="text-align:left">
        <div>العميل: <strong id="customerName">-</strong></div>
        <div>الهاتف: <span id="customerPhone">-</span></div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>الصنف</th>
          <th style="text-align:center">الكمية</th>
          <th style="text-align:left">السعر</th>
        </tr>
      </thead>
      <tbody id="itemsBody"></tbody>
    </table>

    <div class="total-row">
      الإجمالي: <span id="totalAmount">0</span> جنيه
    </div>

    <div id="notesBox" style="display:none;font-size:12px;color:#757575;margin-top:12px"></div>

    <div class="footer">
      شكرًا لزيارتكم • نتمنى لكم وجبة شهية<br>
      نستقبل كافة المناسبات والطلبات
    </div>
  </div>

  <div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ طباعة الفاتورة</button>
    <a class="btn-back" href="?page=cashier">رجوع</a>
  </div>

  <script>
    function getQueryParam(name) {
      const url = new URL(window.location.href);
      return url.searchParams.get(name) || '';
    }

    const orderId = getQueryParam('id');
    if (!orderId) {
      document.getElementById('invoiceBox').innerHTML = '<p style="text-align:center;padding:40px">لم يتم تحديد رقم الطلب</p>';
    } else {
      api('get_invoice', { orderId }).then(res => {
        if (!res.success) {
          document.getElementById('invoiceBox').innerHTML = '<p style="text-align:center;padding:40px">' + (res.message || 'خطأ') + '</p>';
          return;
        }
        const o = res.order;
        const r = res.restaurant;
        document.getElementById('restName').textContent = r.name;
        document.getElementById('restAddress').textContent = r.address;
        document.getElementById('restPhone').textContent = '☎ ' + r.phone;
        document.getElementById('orderId').textContent = o.order_id;
        document.getElementById('orderDate').textContent = o.created_at || '-';
        document.getElementById('customerName').textContent = o.customer_name || '-';
        document.getElementById('customerPhone').textContent = o.phone || '-';
        document.getElementById('totalAmount').textContent = o.total;

        const tbody = document.getElementById('itemsBody');
        tbody.innerHTML = '';
        (o.items || []).forEach(it => {
          const tr = document.createElement('tr');
          const tdName = document.createElement('td');
          tdName.textContent = it.name;
          const tdQty = document.createElement('td');
          tdQty.style.textAlign = 'center';
          tdQty.textContent = it.qty;
          const tdPrice = document.createElement('td');
          tdPrice.style.textAlign = 'left';
          tdPrice.textContent = (it.price * it.qty);
          tr.append(tdName, tdQty, tdPrice);
          tbody.appendChild(tr);
        });

        if (o.notes) {
          document.getElementById('notesBox').style.display = 'block';
          document.getElementById('notesBox').textContent = 'ملاحظات: ' + o.notes;
        }
      }).catch(e => {
        document.getElementById('invoiceBox').innerHTML = '<p style="text-align:center;padding:40px">خطأ في الاتصال بالسيرفر</p>';
      });
    }
  </script>
</body>
</html>
