<?php

namespace App\Dto;

class PaginationDto
{
    /**
     * @var int $page
     */
    private int $page = 1;

    /**
     * @var int $limit
     */
    private int $limit = 10;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return PaginationDto
     */
    public function setPage(int $page): PaginationDto
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return PaginationDto
     */
    public function setLimit(int $limit): PaginationDto
    {
        $this->limit = $limit;
        return $this;
    }
}
