<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# MeatDay.shop - Cart Functionality Documentation

## 🛒 Cart System Overview

The cart system allows users to add multiple products with different order types (immediate or pre-order), apply discounts, and earn reward points based on purchase type.

## 📊 Database Schema

### Cart Table (`cart`)
```sql
- id (UUID, Primary Key)
- user_id (UUID, Foreign Key to users.user_id)
- product_id (UUID, Foreign Key to products.id)
- quantity (DECIMAL(8,3)) - Supports fractional kg (e.g., 0.500 for 500g)
- unit_price (DECIMAL(10,2)) - Price per kg from product
- discount_percentage (DECIMAL(5,2)) - Product discount percentage
- discount_amount (DECIMAL(10,2)) - Calculated discount amount
- final_price (DECIMAL(10,2)) - Price after discount
- total_price (DECIMAL(10,2)) - Quantity * final_price
- regular_points (INTEGER) - Points for immediate purchase
- pre_order_points (INTEGER) - Points for pre-order
- order_type (ENUM: 'immediate', 'pre_order')
- pre_order_date (DATE, NULLABLE) - Future delivery date for pre-orders
- timestamps
```

### Transactions Table (`transactions`)
```sql
- id (UUID, Primary Key)
- user_id (UUID, Foreign Key to users.user_id)
- bill_no (STRING, UNIQUE)
- subtotal (DECIMAL(10,2)) - Total before discount
- total_discount (DECIMAL(10,2)) - Total discount applied
- total_amount (DECIMAL(10,2)) - Final amount after discount
- order_type (ENUM: 'immediate', 'pre_order')
- delivery_date (DATE, NULLABLE) - For pre-orders
- status (ENUM: 'pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled')
- total_regular_points (INTEGER) - Total regular points earned
- total_pre_order_points (INTEGER) - Total pre-order points earned
- timestamps
```

### Transaction Items Table (`transaction_items`)
```sql
- id (UUID, Primary Key)
- transaction_id (UUID, Foreign Key to transactions.id)
- product_id (UUID, Foreign Key to products.id)
- quantity (DECIMAL(8,3))
- unit_price (DECIMAL(10,2))
- discount_percentage (DECIMAL(5,2))
- discount_amount (DECIMAL(10,2))
- final_price (DECIMAL(10,2))
- total_price (DECIMAL(10,2))
- regular_points (INTEGER)
- pre_order_points (INTEGER)
- order_type (ENUM: 'immediate', 'pre_order')
- timestamps
```

### Reward Points Table (`reward_points`)
```sql
- id (UUID, Primary Key)
- user_id (UUID, Foreign Key to users.user_id)
- points (INTEGER)
- point_type (ENUM: 'regular', 'pre_order')
- transaction_id (UUID, NULLABLE, Foreign Key to transactions.id)
- redeemed (BOOLEAN, DEFAULT FALSE)
- redeem_date (TIMESTAMP, NULLABLE)
- timestamps
```

## 🎯 Cart Functionality Requirements

### 1. **Product Price Handling**
- **`price_per_kg`**: Base price per kilogram from admin
- **`product_discount`**: Discount percentage applied to the product
- **Discount Calculation**: `discount_amount = (unit_price * discount_percentage) / 100`
- **Final Price**: `final_price = unit_price - discount_amount`

### 2. **Reward Points System**
- **`regular_points`**: Points earned for immediate purchases (buy now)
- **`pre_order_points`**: Points earned for pre-orders (future delivery)
- **Point Calculation**: `points = product_points * quantity`

### 3. **Order Types**
- **Immediate Order**: 
  - Earns `regular_points`
  - No delivery date required
  - Points added immediately after purchase
- **Pre-Order**: 
  - Earns `pre_order_points`
  - Requires delivery date (minimum 2 days from today)
  - Points added after successful delivery

### 4. **Cart Calculations**
- **Subtotal**: Sum of all item total prices
- **Total Discount**: Sum of all item discount amounts
- **Total Regular Points**: Sum of regular points from immediate orders
- **Total Pre-Order Points**: Sum of pre-order points from pre-orders

## 🚀 API Endpoints

### Cart Management
```
POST   /api/cart/add                    - Add item to cart
PUT    /api/cart/update/{cartItemId}    - Update cart item quantity
DELETE /api/cart/remove/{cartItemId}    - Remove item from cart
GET    /api/cart                        - View user's cart
DELETE /api/cart/clear                  - Clear entire cart
GET    /api/cart/item/{cartItemId}      - Get specific cart item
```

### Cart Response Format
```json
{
  "success": true,
  "data": {
    "cart": {
      "items": [...],
      "total_items": 3,
      "total_quantity": 2.5,
      "subtotal": 1250.00,
      "total_discount": 125.00,
      "total_regular_points": 150,
      "total_pre_order_points": 200,
      "immediate_items": [...],
      "pre_order_items": [...]
    }
  },
  "message": "Cart retrieved successfully"
}
```

## 📝 Cart Item Example

### Add to Cart Request
```json
{
  "product_id": "uuid",
  "quantity": 1.5,
  "order_type": "immediate"
}
```

### Pre-Order Request
```json
{
  "product_id": "uuid",
  "quantity": 2.0,
  "order_type": "pre_order",
  "pre_order_date": "2025-08-10"
}
```

### Cart Item Response
```json
{
  "id": "cart-item-uuid",
  "user_id": "user-uuid",
  "product_id": "product-uuid",
  "quantity": 1.5,
  "unit_price": 500.00,
  "discount_percentage": 10.00,
  "discount_amount": 50.00,
  "final_price": 450.00,
  "total_price": 675.00,
  "regular_points": 75,
  "pre_order_points": 0,
  "order_type": "immediate",
  "pre_order_date": null,
  "product": {
    "id": "product-uuid",
    "name": "Chicken Breast",
    "price_per_kg": 500.00,
    "product_discount": 10.00,
    "regular_points": 50,
    "pre_order_points": 75,
    "image_url": "https://domain.com/storage/product_images/image.jpg"
  }
}
```

## 🔄 Cart Lifecycle

1. **Add to Cart**: User adds products with quantity and order type
2. **Update Cart**: Modify quantities or order types
3. **View Cart**: See cart summary with all calculations
4. **Checkout**: Convert cart to transaction
5. **Clear Cart**: Remove all items after successful purchase

## 🎁 Reward Points Flow

### Immediate Orders
1. User adds item to cart with `order_type: 'immediate'`
2. Regular points calculated: `product.regular_points * quantity`
3. Points added to user account immediately after purchase

### Pre-Orders
1. User adds item to cart with `order_type: 'pre_order'` and delivery date
2. Pre-order points calculated: `product.pre_order_points * quantity`
3. Points added to user account after successful delivery
4. Minimum delivery date: 2 days from today

## 🛡️ Validations

### Cart Item Validations
- **Quantity**: Minimum 0.001 kg (1 gram)
- **Product**: Must exist and be active
- **Pre-order Date**: Must be at least 2 days from today
- **Unique Items**: Same product + order type + delivery date = update quantity

### Business Rules
- **Discount**: Applied per kg, then multiplied by quantity
- **Points**: Earned based on quantity and order type
- **Pre-orders**: Cannot be delivered same day or next day
- **Cart Limits**: No maximum limit (can be added if needed)

## 🔧 Technical Implementation

### Cart Model Features
- **Automatic Calculations**: Prices and points calculated automatically
- **Relationships**: Links to User and Product models
- **Validation**: Business logic validation
- **Summary Methods**: Cart summary with totals

### Controller Features
- **CRUD Operations**: Add, update, remove, view cart items
- **Validation**: Input validation and business rule enforcement
- **Error Handling**: Proper error responses
- **Security**: User-specific cart access

## 📊 Database Relationships

```
users (1) ←→ (many) cart
products (1) ←→ (many) cart
users (1) ←→ (many) transactions
transactions (1) ←→ (many) transaction_items
products (1) ←→ (many) transaction_items
users (1) ←→ (many) reward_points
transactions (1) ←→ (many) reward_points
```

## 🚀 Next Steps

1. **Run Migrations**: `php artisan migrate:fresh --seed`
2. **Test Cart API**: Use the provided endpoints
3. **Implement Checkout**: Convert cart to transaction
4. **Add Reward Points**: Implement points earning system
5. **Frontend Integration**: Connect to mobile/web app

## ❓ Questions for Clarification

1. **Cart Limits**: Should there be a maximum number of items or total value?
2. **Discount Rules**: Any special discount rules for bulk orders?
3. **Point Expiry**: Do reward points expire? If yes, after how long?
4. **Delivery Slots**: Should we implement delivery time slots for pre-orders?
5. **Payment Integration**: How should payment be integrated with cart checkout?
6. **Inventory Check**: Should we check product availability before adding to cart?
7. **Cart Persistence**: Should cart persist across sessions/devices?

---

*This documentation covers the complete cart functionality implementation. All calculations, validations, and business rules are implemented as specified.*
