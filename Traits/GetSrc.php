<?php

namespace Modules\File\Traits;

use Illuminate\Database\Eloquent\Model;

trait GetSrc
{

    /**
     * возвращаем массив первое изображение для объекта
     *
     * @param $items
     *
     * @return array|null
     */
    public function modelPicture($items): ?array
    {
        $picture = [];
        if (!empty($items->filesImageActual))
        {
            if(null !== $items->filesImageActual->sortBy('order')->first())
            {
                $picture = $items->filesImageActual->sortBy('order')->first()->picture;
            }
        }

        return $picture;
    }

    /**
     * возвращаем массив доступных изображение для объекта
     *
     * @param Model $items
     *
     * @return array|null
     */
    public function modelPictures(Model $items): ?array
    {
        foreach ($items->filesImageActual->sortBy('order') as $item)
        {
            $pictures[] = $item->picture;
        }
        return $pictures ?? [];
    }

    /**
     * @param Model $items
     *
     * @return string|null
     */
     public function original(Model $items): ?string
     {
        if (isset($items->filesImageActual))
        {
            return $this->getFirstFile($items)->src_original ?? null;
        } else
        {
            return null;
        }
    }

    /**
     * @param Model $items
     *
     * @return string|null
     */
    public function xs(Model $items): ?string
    {
        if (isset($items->filesImageActual))
        {
            return $this->getFirstFile($items)->src_xs ?? null;
        } else
        {
            return null;
        }
    }

    /**
     * @param Model $items
     *
     * @return string|null
     */
    public function sm(Model $items): ?string
    {
        if (isset($items->filesImageActual))
        {
            return $this->getFirstFile($items)->src_sm ?? null;
        } else
        {
            return null;
        }
    }

    /**
     * @param Model $items
     *
     * @return string|null
     */
    public function md(Model $items): ?string
    {
        if (isset($items->filesImageActual))
        {
            return $this->getFirstFile($items)->src_md ?? null;
        } else
        {
            return null;
        }
    }

    /**
     * @param Model $items
     *
     * @return string|null
     */
    public function lg(Model $items): ?string
    {
        if (isset($items->filesImageActual))
        {
            return $this->getFirstFile($items)->src_lg ?? null;
        } else
        {
            return null;
        }
    }

    /**
     * @param Model $items
     *
     * @return array|null
     */
    private function getFirstFile(Model $items): ?array
    {
        return array_first($items->filesImageActual->sortBy('order')) ?? null;
    }
}