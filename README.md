<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Database Transaction on Laravel

With Database Transaction, database will not save changes when there is an error on other query result. Example

- There is 2 query, one is for update Product QTY and another one is for add to transaction table. Let say our query is

```php
$query_one = \App\Models\Product::findOrFail($request->product_id);
$query_one->qty -= $request->qty;
$query_one->save();
```

And our second query is

```php
$query_two = new \App\Models\Transaction();
$query_two->product_id = $query_one->id;
$query_two->qty = $request->qty;
$query_two->price = $query_one->price;
$query_two->save();
```

- Let say this is our product data

```text
{
    id: 1,
    name: "Sepatu",
    qty: 20,
    price: 350000
}
```

then, when we run our function without database transaction, and we make an error on `$query_two`,

Example request
```text
{
    product_id: 1,
    qty: 2
}
```

```php
$query_one = \App\Models\Product::findOrFail($request->product_id);
$query_one->qty -= $request->qty;
$query_one->save();

$query_two = new \App\Models\Transaction();
$query_two->product_id = $query_one->id;
$query_two->qty = $request->qty;
// $query_two->price = $query_one->price;
$query_two->save();
```

The result is on `$query_two` we got an error where `price` didn't have default value, but our `qty` on `$query_one` is already decrease

```text
{
    id: 1,
    name: "Sepatu",
    qty: 18,
    price: 350000
}
```

But we don't have any data on `transactions` table. So now we will use database transaction with same code and request. We still face an error, but when we check the products table data, no changes where made

```text
{
    id: 1,
    name: "Sepatu",
    qty: 18, //remember our last request? qty is decreased by 2, but after an error with database transaction, the value still at 18 => that means nothing changed
    price: 350000
}
```

When we run on query below, we will see changes made

```php
$query_one = \App\Models\Product::findOrFail($request->product_id);
$query_one->qty -= $request->qty;
$query_one->save();

$query_two = new \App\Models\Transaction();
$query_two->product_id = $query_one->id;
$query_two->qty = $request->qty;
$query_two->price = $query_one->price;
$query_two->save();
```

Final result is

```text
{
    // product table
    id: 1,
    name: "Sepatu",
    qty: 16,
    price: 350000
}, {
    // transaction table
    id: 1,
    product_id: 1,
    qty: 2,
    price: 350000
}
```

## Request

All request is run on api environment

### Product

- Endpoint: `http://127.0.0.1:8000/api/product`,
- Method: `POST`,
- Request Body:
```
name:Sepatu
qty:20
price:350000
```

### Transaction
- Endpoint: `http://127.0.0.1:8000/api/transaction`,
- Method: `POST`,
- Request Body:
```
product_id:1
qty:2
```
