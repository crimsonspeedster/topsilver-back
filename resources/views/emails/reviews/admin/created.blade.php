<h1>Новый отзыв ожидает проверки.</h1>

<p>Товар: {{$productReview->product->title}}</p>
<p>Пользователь: {{$productReview->user->profile->name}} {{$productReview->user->profile->surname}}</p>

<p>
    <a href="{{ url('/nova/resources/product-reviews/' . $productReview->id) }}">
        👉 Открыть в админке
    </a>
</p>
