<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Create Order';

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

// Fetch all customers for phone number autocomplete or selection if needed
$stmt = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
$customers = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div style="max-width: 1000px; margin: auto;">

    <!-- Alert Container -->
    <div id="alert-container"></div>

    <!-- Top Card: Add Item Form -->
    <div class="card" style="display: block; margin-bottom: 20px; border-radius: 4px; padding: 0;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #fdfdfd;">
            <h4 style="margin: 0; color: #333; font-weight: 600; font-size: 18px;">Create Order</h4>
            <a href="../orders/index.php" style="background: #dc3545; color: white; padding: 6px 15px; border-radius: 4px; text-decoration: none; font-size: 14px;">Back</a>
        </div>
        <div style="padding: 20px; display: flex; gap: 20px; align-items: flex-end;">
            <div style="flex: 2;">
                <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Scan Barcode</label>
                <input type="text" id="barcode_input" placeholder="Scan barcode here..." style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; background: #e9ecef;" autofocus>
            </div>
            <div style="flex: 2;">
                <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Or Select Product</label>
                <select id="product_select" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                    <option value="">-- Select Product --</option>
                    <?php foreach ($products as $prod):
                        $variation = implode(', ', array_filter([$prod['color'] ?? '', $prod['size'] ?? '']));
                        $display_name = $prod['name'] . ($variation ? " ($variation)" : "");
                    ?>
                        <option value="<?php echo $prod['id']; ?>" data-barcode="<?php echo htmlspecialchars($prod['barcode'] ?? ''); ?>" data-name="<?php echo htmlspecialchars($display_name); ?>" data-price="<?php echo $prod['selling_price']; ?>" data-stock="<?php echo $prod['stock_quantity']; ?>">
                            <?php echo htmlspecialchars($display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Quantity <span id="available_stock_display" style="color:#dc3545; font-weight:bold; float:right; font-size: 11px; margin-top: 2px;"></span></label>
                <input type="number" id="qty_input" value="1" min="1" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">GST (%)</label>
                <select id="item_gst" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                    <option value="0" selected>0% (No GST)</option>
                    <option value="5">5%</option>
                    <option value="12">12%</option>
                    <option value="18">18%</option>
                    <option value="28">28%</option>
                </select>
            </div>
            <div>
                <button type="button" onclick="addItem()" style="background: #0d6efd; color: white; border: none; padding: 9px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">Add Item</button>
            </div>
        </div>
    </div>

    <!-- Bottom Card: Cart & Checkout -->
    <div class="card" style="display: block; border-radius: 4px; padding: 0;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #ddd; background: #fdfdfd;">
            <h4 style="margin: 0; color: #333; font-weight: 600; font-size: 18px;">Products</h4>
        </div>

        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 1px solid #ddd; background: #fcfcfc;">
                        <th style="padding: 12px 20px; color: #333; font-weight: 600;">ID</th>
                        <th style="padding: 12px 20px; color: #333; font-weight: 600;">Product Name</th>
                        <th style="padding: 12px 20px; color: #333; font-weight: 600;">Price</th>
                        <th style="padding: 12px 20px; color: #333; font-weight: 600;">Quantity</th>
                        <th style="padding: 12px 20px; color: #333; font-weight: 600;">GST</th>
                        <th style="padding: 12px 20px; color: #333; font-weight: 600;">Total Price</th>
                        <th style="padding: 12px 20px; color: #333; font-weight: 600;">Remove</th>
                    </tr>
                </thead>
                <tbody id="cart-table-body">
                    <tr>
                        <td colspan="7" style="padding: 20px; text-align: center; color: #666; font-weight: 600;">No Items added</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Checkout Section -->
        <div id="checkout-section" style="display: none; padding: 20px; border-top: 1px solid #ddd; background: #fff;">
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Payment Mode *</label>
                    <select id="payment_method" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                        <option value="Cash">Cash</option>
                        <option value="Online">Online</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Customer Phone</label>
                    <input type="text" id="customer_phone" placeholder="Phone Number" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Customer Name</label>
                    <input type="text" id="customer_name" placeholder="Name (Optional)" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Email ID</label>
                    <input type="email" id="customer_email" placeholder="Email (Optional)" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; font-size: 13px; color: #555; margin-bottom: 5px;">Discount</label>
                    <div style="display: flex;">
                        <input type="number" id="discount_input" value="" min="0" step="0.01" oninput="updateTotals()" onkeyup="updateTotals()" onchange="updateTotals()" placeholder="Enter Discount (0)" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-right: none; border-radius: 4px 0 0 4px; font-size: 14px;">
                        <select id="discount_type" onchange="updateTotals()" style="padding: 8px 10px; border: 1px solid #ccc; border-radius: 0 4px 4px 0; background: #f8f9fa; font-size: 14px; cursor: pointer; font-weight: 600;">
                            <option value="amount">₹</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>
                <div style="flex: 1; min-width: 160px;">
                    <label style="display: block; font-size: 13px; color: #0f5132; font-weight: 600; margin-bottom: 5px;">Final Payment (₹)</label>
                    <input type="text" id="final_payment_display" value="₹0.00" readonly style="width: 100%; padding: 8px 12px; border: 2px solid #198754; border-radius: 4px; font-size: 16px; font-weight: 700; color: #198754; background: #e8f5e9;">
                </div>
                <div style="flex: 1; min-width: 180px; display: flex; align-items: flex-end;">
                    <button type="button" id="proceed_order_btn" onclick="checkout()" style="width: 100%; background: #ffc107; color: #000; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 15px; font-weight: 600;">Proceed to Order</button>
                </div>
            </div>

            <div style="margin-top: 15px; text-align: right; font-size: 16px; color: #666; line-height: 1.6;">
                Subtotal (Actual Price): ₹<span id="sub-total-display">0.00</span><br>
                GST Amount: +₹<span id="gst-amount-display">0.00</span><br>
                <span id="discount-row" style="color: #dc3545; font-weight: 600; display: none;">Discount: -₹<span id="discount-amount-display">0.00</span><br></span>
            </div>
            <div style="text-align: right; font-size: 20px; font-weight: 700; color: #198754; margin-top: 5px; border-top: 1px dashed #ccc; padding-top: 10px; display: inline-block; float: right;">
                Payable Grand Total: ₹<span id="grand-total-display">0.00</span>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>

<script>
    let cart = [];

    function showAlert(message, type = 'success') {
        const alertBox = document.createElement('div');
        const bgColor = type === 'success' ? '#d1e7dd' : '#f8d7da';
        const textColor = type === 'success' ? '#0f5132' : '#842029';
        const borderColor = type === 'success' ? '#badbcc' : '#f5c2c7';

        alertBox.style.cssText = `background-color: ${bgColor}; color: ${textColor}; border: 1px solid ${borderColor}; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;`;
        alertBox.innerHTML = `
        <span>${message}</span>
        <span style="cursor: pointer; font-size: 20px; font-weight: bold; line-height: 1;" onclick="this.parentElement.remove()">×</span>
    `;

        const container = document.getElementById('alert-container');
        container.innerHTML = ''; // Clear previous
        container.appendChild(alertBox);

        setTimeout(() => {
            if (alertBox.parentElement) alertBox.remove();
        }, 3000);
    }

    document.getElementById('barcode_input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            if (barcode === '') return;

            const select = document.getElementById('product_select');
            let found = false;

            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].getAttribute('data-barcode') === barcode) {
                    select.selectedIndex = i;
                    found = true;
                    break;
                }
            }

            if (found) {
                updateAvailableStockDisplay();
                addItem();
            } else {
                showAlert('Product with this barcode not found', 'error');
            }

            // Clear input and refocus
            this.value = '';
            this.focus();
        }
    });

    document.getElementById('product_select').addEventListener('change', function() {
        updateAvailableStockDisplay();
    });

    function updateAvailableStockDisplay() {
        const select = document.getElementById('product_select');
        const display = document.getElementById('available_stock_display');
        if (select.value === '') {
            display.innerText = '';
            return;
        }
        const option = select.options[select.selectedIndex];
        const maxStock = parseInt(option.getAttribute('data-stock')) || 0;

        const id = select.value;
        let existing = cart.find(item => item.id === id);
        let inCart = existing ? existing.qty : 0;

        let available = maxStock - inCart;
        display.innerText = `(Available: ${available})`;
    }

    function addItem() {
        const select = document.getElementById('product_select');
        const qtyInput = document.getElementById('qty_input');

        if (select.value === '') {
            showAlert('Please select a product first', 'error');
            return;
        }

        const id = select.value;
        const option = select.options[select.selectedIndex];
        const name = option.getAttribute('data-name');
        const price = parseFloat(option.getAttribute('data-price'));
        const stock = parseInt(option.getAttribute('data-stock'));
        const gstRate = parseFloat(document.getElementById('item_gst').value) || 0;
        let qty = parseInt(qtyInput.value);

        if (isNaN(qty) || qty < 1) {
            qty = 1;
        }

        let existing = cart.find(item => item.id === id);
        let inCart = existing ? existing.qty : 0;

        if (inCart + qty > stock) {
            showAlert(`Not enough stock! Only ${stock - inCart} left.`, 'error');
            return;
        }

        if (existing) {
            existing.qty += qty;
            // Update gst rate in case it changed
            existing.gst_rate = gstRate;
        } else {
            cart.push({
                id,
                name,
                price,
                qty,
                stock,
                gst_rate: gstRate
            });
        }

        showAlert(`Item Added ${name}`, 'success');

        // Reset inputs
        select.value = '';
        qtyInput.value = '1';
        document.getElementById('item_gst').value = '0';
        updateAvailableStockDisplay();

        renderCart();
    }

    function updateQty(id, change) {
        let item = cart.find(i => i.id === id);
        if (item) {
            let newQty = item.qty + change;
            if (newQty < 1) return;

            if (newQty > item.stock) {
                showAlert(`Not enough stock! Max available is ${item.stock}.`, 'error');
                return;
            }

            item.qty = newQty;
            renderCart();
            updateAvailableStockDisplay();
        }
    }

    function updateItemGst(id, rate) {
        let item = cart.find(i => i.id === id);
        if (item) {
            item.gst_rate = parseFloat(rate) || 0;
            renderCart();
        }
    }

    function removeItem(id) {
        cart = cart.filter(item => item.id !== id);
        renderCart();
        updateAvailableStockDisplay();
    }

    function renderCart() {
        const tbody = document.getElementById('cart-table-body');
        const checkoutSection = document.getElementById('checkout-section');

        if (cart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="padding: 20px; text-align: center; color: #666; font-weight: 600;">No Items added</td></tr>';
            checkoutSection.style.display = 'none';
            return;
        }

        let html = '';

        cart.forEach((item, index) => {
            let item_gst_rate = parseFloat(item.gst_rate) || 0;
            item.gst_rate = item_gst_rate;

            let sub = item.price * item.qty;
            let itemGstAmount = (sub * item_gst_rate) / 100;
            let itemTotal = sub + itemGstAmount;

            html += `
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 20px;">${index + 1}</td>
            <td style="padding: 12px 20px; display: flex; align-items: center; gap: 10px;">
                ${item.name}
            </td>
            <td style="padding: 12px 20px;">₹${item.price}</td>
            <td style="padding: 12px 20px;">
                <div style="display: inline-flex; border: 1px solid #ccc; border-radius: 4px; overflow: hidden; background: #fff;">
                    <button onclick="updateQty('${item.id}', -1)" style="border: none; background: #f8f9fa; padding: 5px 10px; cursor: pointer; border-right: 1px solid #ccc; font-weight: bold;">-</button>
                    <input type="text" value="${item.qty}" readonly style="width: 40px; text-align: center; border: none; outline: none; background: white;">
                    <button onclick="updateQty('${item.id}', 1)" style="border: none; background: #f8f9fa; padding: 5px 10px; cursor: pointer; border-left: 1px solid #ccc; font-weight: bold;">+</button>
                </div>
            </td>
            <td style="padding: 12px 20px;">
                <select onchange="updateItemGst('${item.id}', this.value)" style="padding: 4px 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;">
                    <option value="0" ${item.gst_rate === 0 ? 'selected' : ''}>0% (No GST)</option>
                    <option value="5" ${item.gst_rate === 5 ? 'selected' : ''}>5%</option>
                    <option value="12" ${item.gst_rate === 12 ? 'selected' : ''}>12%</option>
                    <option value="18" ${item.gst_rate === 18 ? 'selected' : ''}>18%</option>
                    <option value="28" ${item.gst_rate === 28 ? 'selected' : ''}>28%</option>
                </select>
                ${itemGstAmount > 0 ? `<div style="font-size: 11px; color: #666; margin-top: 2px;">+₹${itemGstAmount.toFixed(2)}</div>` : ''}
            </td>
            <td style="padding: 12px 20px; font-weight: 600;">₹${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td style="padding: 12px 20px;">
                <button onclick="removeItem('${item.id}')" style="background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 13px;">Remove</button>
            </td>
        </tr>
        `;
        });

        tbody.innerHTML = html;
        checkoutSection.style.display = 'block';
        updateTotals();
    }

    function getDiscountAmount(totalBill) {
        let val = parseFloat(document.getElementById('discount_input').value);
        if (isNaN(val) || val < 0) val = 0;
        let type = document.getElementById('discount_type').value;

        let discountAmt = 0;
        if (type === 'percent') {
            if (val > 100) val = 100;
            discountAmt = (totalBill * val) / 100;
        } else {
            discountAmt = val;
        }

        if (discountAmt > totalBill) {
            discountAmt = totalBill;
        }
        return discountAmt;
    }

    function updateTotals() {
        let subTotal = 0;
        let totalGstAmount = 0;

        cart.forEach(item => {
            let sub = item.price * item.qty;
            let item_gst_rate = parseFloat(item.gst_rate) || 0;
            let itemGstAmount = (sub * item_gst_rate) / 100;

            subTotal += sub;
            totalGstAmount += itemGstAmount;
        });

        let totalBill = subTotal + totalGstAmount;
        let discountAmt = getDiscountAmount(totalBill);
        let grandTotal = totalBill - discountAmt;
        if (grandTotal < 0) grandTotal = 0;

        document.getElementById('sub-total-display').innerText = subTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        document.getElementById('gst-amount-display').innerText = totalGstAmount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        const discountRow = document.getElementById('discount-row');
        if (discountAmt > 0) {
            discountRow.style.display = 'inline';
            document.getElementById('discount-amount-display').innerText = discountAmt.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else {
            discountRow.style.display = 'none';
        }

        const formattedGrandTotal = grandTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('grand-total-display').innerText = formattedGrandTotal;

        const finalPaymentInput = document.getElementById('final_payment_display');
        if (finalPaymentInput) {
            finalPaymentInput.value = '₹' + formattedGrandTotal;
        }

        const proceedBtn = document.getElementById('proceed_order_btn');
        if (proceedBtn) {
            proceedBtn.innerText = 'Proceed to Order (₹' + formattedGrandTotal + ')';
        }
    }

    function checkout() {
        if (cart.length === 0) {
            showAlert("Cart is empty!", "error");
            return;
        }

        let paymentMode = document.getElementById('payment_method').value;
        if (paymentMode === '') {
            showAlert("Please select a payment mode!", "error");
            return;
        }

        let customerPhone = document.getElementById('customer_phone').value;
        let customerName = document.getElementById('customer_name').value;
        let customerEmail = document.getElementById('customer_email').value;

        let totalBill = cart.reduce((sum, item) => {
            let sub = item.price * item.qty;
            let gst = (sub * (parseFloat(item.gst_rate) || 0)) / 100;
            return sum + sub + gst;
        }, 0);
        let discountAmt = getDiscountAmount(totalBill);

        let data = {
            customer_phone: customerPhone,
            customer_name: customerName,
            customer_email: customerEmail,
            payment_method: paymentMode,
            items: cart,
            discount: parseFloat(discountAmt.toFixed(2))
        };

        fetch('checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    window.location.href = 'receipt.php?id=' + res.order_id;
                } else {
                    showAlert("Error: " + res.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showAlert("Checkout failed. Check console.", "error");
            });
    }
</script>

<?php require_once '../includes/footer.php'; ?>