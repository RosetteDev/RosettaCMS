# E-commerce Foundation

This foundation gives a site the ability to sell products and record orders.
It is deliberately scoped: it ships the catalog, cart, checkout, orders and
coupon/tax plumbing with a **manual payment** workflow, plus a clean seam for
a paid module to take over payment handling later.

## What's included

- **Products** — admin CRUD with price, sale price, stock, SKU, category and
  draft/active status. Only active products are sellable.
- **Cart** — session-based, per-visitor. Quantities are capped at available
  stock.
- **Checkout** — validates the cart, applies coupons and tax, creates an
  order in a single transaction, decrements stock, and clears the cart.
- **Orders** — unique `ORD-YYYY-NNNNN` order numbers, item snapshots (name,
  SKU, unit price) so orders survive product deletion, and an admin dashboard
  with status transitions.
- **Coupons** — percent or fixed discounts, minimum subtotal, max uses and
  expiry. Codes are stored uppercase.
- **Tax** — a site-wide rate (%) in Site Settings, applied to the
  post-discount subtotal.
- **Storefront** — a self-contained, dependency-free storefront
  (`/products`, `/products/{slug}`, `/cart`, `/checkout`, order success page)
  with no theme coupling. Theme-integrated storefront rendering is a follow-up.

## Payment seam

Checkout never talks to a gateway directly. It goes through the
`PaymentGateway` contract (`app/Services/Payments/PaymentGateway.php`):

```php
$result = app(\App\Services\Payments\PaymentService::class)->charge($order);
```

The foundation binds `ManualPaymentGateway` (orders are recorded with a
`pending` payment status for an offline/invoice workflow). A paid module:

1. implements the `PaymentGateway` contract (e.g. Stripe), and
2. sets `PAYMENTS_DEFAULT` (or `payments.default` in `config/payments.php`)
   to its class name.

No checkout or order code changes are needed. Payment failures never cancel
an order — the order is recorded and the gateway result is applied
best-effort.

## Deliberately out of scope

Variants/options, inventory beyond a simple stock counter, shipping/tax
calculation beyond the site tax rate, email/SMS notifications, and live
payment processing (Stripe/PayPal). These are intentionally left to the paid
module.

## Admin

Products, Coupons and Orders live in the admin sidebar (admin-only). The
E-commerce section of Site Settings holds the tax rate.

## Key files

- `app/Http/Controllers/StorefrontController.php` — storefront + checkout
- `app/Services/CartService.php` — session cart
- `app/Services/CheckoutService.php` — totals + order placement
- `app/Services/Payments/*` — payment seam (contract, manual gateway, service)
- `app/Http/Controllers/Admin/{Product,Coupon,Order}Controller.php`
- `resources/views/storefront/*` — self-contained storefront views
- `config/payments.php` — gateway selection
