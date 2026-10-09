<x-mail::message>
Ваше замовлення #{{ $order->id }} оновлено!

<x-mail::button :url="frontend_url('/order/details/' . $order->public_token)">
Переглянути замовлення
</x-mail::button>

Дякуємо,<br>
{{ config('app.name') }}
</x-mail::message>
