<?php
require '_base.php';
auth('Member');
 
$product_id = (int)(post('product_id') ?: get('id'));
$qty        = max(1, (int)(post('quantity') ?: 1)); 
 
$p = get_product($product_id);
 
if (!$p) {
    temp('info', 'Product not found.');
    redirect('product_list.php');
}
 
$stmt = $conn->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ?');
$stmt->execute([$_user->user_id, $product_id]); 
$existing = $stmt->fetch();
 
if ($existing) {
    $stmt = $conn->prepare('UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?');
    $stmt->execute([$qty, $existing->cart_id]);
} else {
    $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
    $stmt->execute([$_user->user_id, $product_id, $qty]);
}
 
temp('info', 'Added to cart!');
redirect('shopping_cart.php');