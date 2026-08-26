<?php

namespace App\Controller\Api;

use App\Controller\AbstractBaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class ApiController extends AbstractBaseController
{
    /** @param array<string, string> $headers */
    protected function jsonResponse(mixed $data, int $status = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return $this->json($data, $status, $headers);
    }

    /** @param list<array<string, mixed>> $violations */
    protected function jsonError(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        array $violations = []
    ): JsonResponse {
        $payload = ['error' => ['message' => $message]];
        if ($violations !== []) {
            $payload['error']['violations'] = $violations;
        }

        return $this->jsonResponse($payload, $status);
    }

    /** @return list<array{property: string, message: string}> */
    protected function violationList(ConstraintViolationListInterface $violations): array
    {
        $list = [];
        foreach ($violations as $violation) {
            $list[] = [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $list;
    }
}
