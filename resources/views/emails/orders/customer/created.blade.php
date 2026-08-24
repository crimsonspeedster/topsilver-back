<x-mail::message>
# Замовлення #{{ $order->id }}

Дякую за замовлення!

**Сума:** {{ $total_formatted }}

<x-mail::button :url="frontend_url('/order/details/' . $order->public_token)">
Переглянути замовлення
</x-mail::button>

Дякуємо,<br>
{{ config('app.name') }}
</x-mail::message>
