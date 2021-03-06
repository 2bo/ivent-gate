<?php

namespace App\Repositories;

use App\DataModels\Tag as TagDataModel;
use App\Domain\Models\Event\Tag;

class TagRepository
{
    //FIXME: このファンクションを廃止する
    public function saveTagsFromNames(array $tagNames)
    {
        foreach ($tagNames as $tagName) {
            TagDataModel::updateOrCreate(['name' => $tagName], ['pattern' => '/' . $tagName . '/u']);
        }
    }

    public function findByName(String $name): ?Tag
    {
        $dataModel = TagDataModel::where('name', $name)->first();
        if (!$dataModel) {
            return null;
        }
        return $dataModel->toDomainModel();
    }

}
