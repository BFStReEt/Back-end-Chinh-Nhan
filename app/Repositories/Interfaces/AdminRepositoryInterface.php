<?php

namespace App\Repositories\Interfaces;

/**
 * Interface UserServiceInterface
 * @package App\Services\Interfaces
 */
interface AdminRepositoryInterface
{
    public function findByAdmin($name);
    public function findByCondition(array $condition = []);
    public function all(array $relation=[]);
    public function create(array $payload = []);
}