<x-mail::message>
# Новий відгук очікує на перевірку

**Товар:** {{ $productReview->product->title }}

**Користувач:** {{ $productReview->user->profile->name }} {{ $productReview->user->profile->surname }}

<x-mail::button :url="url('/admin/resources/product-reviews/' . $productReview->id)">
Відкрити в адмін-панелі
</x-mail::button>
</x-mail::message>
