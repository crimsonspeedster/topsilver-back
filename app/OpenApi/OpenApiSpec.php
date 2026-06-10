<?php
namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API documentation for 1c integration in TopSilver project",
    title: "API for 1c integration in TopSilver project",
)]
#[OA\Server(
    url: "https://api.top-silver.ua/",
    description: "Api server"
)]
class OpenApiSpec {}
