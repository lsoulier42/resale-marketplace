<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractBaseController extends AbstractController
{
    /**
     * @param Request $request
     * @return PaginationDto
     */
    public static function createPaginationDto(
        Request $request
    ): PaginationDto {
        $dto = new PaginationDto();
        $dto->setPage((int)$request->query->get('page', '1'))
            ->setLimit((int)$request->query->get('limit', '10'));
        return $dto;
    }

    /**
     * @param string $message
     * @return void
     */
    public function addSuccessMessage(
        string $message
    ): void {
        $this->addFlash('success', $message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function addWarningMessage(
        string $message
    ): void {
        $this->addFlash('warning', $message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function addErrorMessage(
        string $message
    ): void {
        $this->addFlash('danger', $message);
    }
}
