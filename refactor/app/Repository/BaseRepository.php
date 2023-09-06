<?php

namespace DTApi\Repository;

use Exception;
use Illuminate\Database\Eloquent\Model;
use DTApi\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class BaseRepository
{
    protected array $validationRules = [];

    public function __construct(protected Model|Job|null $model = null)
    {}

    public function validatorAttributeNames(): array
    {
        return [];
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return Collection|Model[]
     */
    public function all(): array|Collection
    {
        return $this->model->all();
    }
    public function find(int $id): Model|null
    {
        return $this->model->find($id);
    }

    public function with($array)
    {
        return $this->model->with($array);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function findBySlug(string $slug): Model
    {
        return $this->model->where('slug', $slug)->first();
    }
    public function query(): Builder
    {
        return $this->model->query();
    }
    public function instance(array $attributes = []): Model
    {
        $model = $this->model;
        return new $model($attributes);
    }

    public function paginate(?int $perPage = null)
    {
        return $this->model->paginate($perPage);
    }

    public function where(string $key, string|\Closure|array $where)
    {
        return $this->model->where($key, $where);
    }
    public function validator(array $data = [], ?array $rules = null, array $messages = [], array $customAttributes = []): Validator
    {
        if (is_null($rules)) {
            $rules = $this->validationRules;
        }

        return Validator::make($data, $rules, $messages, $customAttributes);
    }

    /**
     * @throws ValidationException
     */
    public function validate(array $data = [], ?array $rules = null, array $messages = [], array $customAttributes = []): bool
    {
        $validator = $this->validator($data, $rules, $messages, $customAttributes);
        return $this->_validate($validator);
    }
    public function create(array $data = []): Model
    {
        return $this->model->create($data);
    }
    public function update(int $id, array $data = []): Model
    {
        $instance = $this->findOrFail($id);
        $instance->update($data);
        return $instance;
    }

    /**
     * @throws Exception
     */
    public function delete(int $id): Model
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return $model;
    }
    protected function _validate(Validator $validator): bool
    {
        if (!empty($attributeNames = $this->validatorAttributeNames())) {
            $validator->setAttributeNames($attributeNames);
        }

        if ($validator->fails()) {
            return false;
        }

        return true;
    }

}