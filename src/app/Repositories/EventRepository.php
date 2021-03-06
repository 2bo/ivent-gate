<?php

namespace App\Repositories;

use App\DataModels\Event as EventDataModel;
use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventRepositoryInterface;
use App\Domain\Models\Prefecture\PrefectureId;

class EventRepository implements EventRepositoryInterface
{

    public function findAll(): array
    {
        $events = [];
        $dataModels = EventDataModel::with('types')->get();
        foreach ($dataModels as $dataModel) {
            $events[] = $this->convertDataModelToEntity($dataModel);
        }
        return $events;
    }

    public function findById(int $id): ?Event
    {
        $dataModel = EventDataModel::find($id);
        if (is_null($dataModel)) {
            return null;
        }
        $event = $this->convertDataModelToEntity($dataModel);
        return $event;
    }

    public function updateOrCreateEvent(Event $event): Event
    {
        $dataModel = EventDataModel::updateOrCreate(
            [
                'site_name' => $event->getSiteName(),
                'event_id' => $event->getEventId()
            ],
            [
                'title' => $event->getTitle(),
                'catch' => $event->getCatch(),
                'description' => $event->getDescription(),
                'prefecture_id' => $event->getPrefectureId() ? $event->getPrefectureId()->value() : null,
                'started_at' => $event->getStartedAt(),
                'ended_at' => $event->getEndedAt(),
                'event_url' => $event->getEventUrl(),
                'limit' => $event->getLimit(),
                'address' => $event->getAddress(),
                'place' => $event->getPlace(),
                'lat' => $event->getLat(),
                'lon' => $event->getLon(),
                'owner_id' => $event->getOwnerId(),
                'owner_nickname' => $event->getOwnerNickname(),
                'owner_twitter_id' => $event->getOwnerTwitterId(),
                'owner_display_name' => $event->getOwnerDisplayName(),
                'group_id' => $event->getGroupId(),
                'participants' => $event->getParticipants(),
                'waiting' => $event->getWaiting(),
                'event_created_at' => $event->getEventCreatedAt(),
                'event_updated_at' => $event->getEventUpdatedAt(),
                'is_online' => $event->isOnline(),
            ]
        );

        //タイプの更新
        $typeIds = [];
        $types = $event->getTypes();
        foreach ($types as $type) {
            $typeIds[] = $type->getId();
        }
        $dataModel->types()->sync($typeIds);

        //タグの更新
        $tagIds = [];
        $tags = $event->getTags();
        foreach ($tags as $tag) {
            $tagIds[] = $tag->getId();
        }
        $dataModel->tags()->sync($tagIds);
        return $event;
    }

    private function convertDataModelToEntity(EventDataModel $eventDataModel): Event
    {
        $types = [];
        foreach ($eventDataModel->types as $type) {
            $types[] = $type->toDomainModel();
        }
        $tags = [];
        foreach ($eventDataModel->tags as $tag) {
            $tags[] = $tag->toDomainModel();
        }

        $event = new Event(
            $eventDataModel->id,
            $eventDataModel->site_name,
            $eventDataModel->event_id,
            $eventDataModel->title,
            $eventDataModel->catch,
            $eventDataModel->description,
            $eventDataModel->event_url,
            $eventDataModel->prefecture_id ? new PrefectureId($eventDataModel->prefecture_id) : null,
            $eventDataModel->address,
            $eventDataModel->place,
            $eventDataModel->lat,
            $eventDataModel->lon,
            $eventDataModel->started_at ? new \DateTime($eventDataModel->started_at) : null,
            $eventDataModel->ended_at ? new \DateTime($eventDataModel->ended_at) : null,
            $eventDataModel->limit,
            $eventDataModel->participants,
            $eventDataModel->waiting,
            $eventDataModel->owner_id,
            $eventDataModel->owner_nickname,
            $eventDataModel->owner_twitter_id,
            $eventDataModel->owner_display_name,
            $eventDataModel->group_id,
            $eventDataModel->event_created_at ? new \DateTime($eventDataModel->event_created_at) : null,
            $eventDataModel->event_updated_at ? new \DateTime($eventDataModel->event_updated_at) : null,
            $eventDataModel->is_online ? true : false,
            $types,
            $tags
        );
        return $event;
    }

}
