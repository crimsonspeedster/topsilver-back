<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API documentation for TopSilver project",
    title: "API for TopSilver project",
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local development server"
)]
class OpenApiSpec {}
