<?php
namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: "/api/v1/integrations/1c/taxonomy/certificates",
    description: "Sync certificates from 1c with site",
    summary: "Add / Update certificates",
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        ref: "#/components/requestBodies/C1CertificateRequestBody"
    ),
    tags: ["certificates"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Successfully response",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "success",
                        type: "boolean",
                        example: true,
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: "Unauthenticated"
        )
    ]
)]
class CertificateAddEndpoint {}
