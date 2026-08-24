<x-mail::message>
# Статус замовлення №{{ $order->id }} змінено

Статус вашого замовлення оновлено:

**{{ $order->status->label() }}**

Ви можете переглянути деталі замовлення за посиланням нижче або в особистому кабінеті, якщо ви зареєстровані на сайті.

<x-mail::button :url="frontend_url('/order/details/' . $order->public_token)">
Переглянути замовлення
</x-mail::button>

Дякуємо, що обираєте {{ config('app.name') }}!

</x-mail::message>
