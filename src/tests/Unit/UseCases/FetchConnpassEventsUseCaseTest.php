<?php

namespace Tests\Unit\UseCases;

use App\Domain\Models\Event\ConnpassEvent;
use App\Domain\Models\Event\ConnpassEventRepositoryInterface;
use App\Repositories\API\ConnpassEventApiRepository;
use App\Repositories\EventRepository;
use App\Repositories\EventTypeRepository;
use App\UseCases\FetchConnpassEvents\FetchConnpassEventsInputData;
use App\UseCases\FetchConnpassEvents\FetchConnpassEventsUseCase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Mockery;

class FetchConnpassEventsUseCaseTest extends TestCase
{
    static private $isDbInitialized = false;

    public function setUp(): void
    {
        parent::setUp();
        if (!self::$isDbInitialized) {
            self::initializeDb();
        }
    }

    private static function initializeDb()
    {
        Artisan::call('migrate:refresh');
        Artisan::call('db:seed');
        self::$isDbInitialized = true;
    }

    public function testHandle()
    {
        // Mockに差し替え
        $mockEvents = $this->getConnpassApiRepositoryMockReturnData();
        $connpassApiRepoMock = Mockery::mock(ConnpassEventApiRepository::class);
        $connpassApiRepoMock->shouldReceive('fetchEvents')->andReturn($mockEvents);
        $this->app->singleton(ConnpassEventRepositoryInterface::class, function () use ($connpassApiRepoMock) {
            return $connpassApiRepoMock;
        });

        $input = new FetchConnpassEventsInputData('202003');
        $useCase = app(FetchConnpassEventsUseCase::class);
        $output = $useCase->handle($input);


        $eventsRepository = new EventRepository();
        $events = $eventsRepository->findAll();
        self::assertEquals(count($mockEvents), $output->getNumOfEvents());
        self::assertEquals(count($mockEvents), count($events));

        self::assertTrue($events[0]->isOnline());
        self::assertNotNull($events[0]->getPrefectureId());
        self::assertEquals(8, count($events[0]->getTypes()));
    }

    private function getConnpassApiRepositoryMockReturnData(): array
    {
        $events = [];
        $typeRepository = new EventTypeRepository();
        $types = $typeRepository->all();

        for ($i = 0; $i < 3; $i++) {
            $events[] = new ConnpassEvent(
                null,
                $i + 1,
                'オンライン' . $i,
                'catch' . $i,
                'description' . $i,
                'http://example.com/' . $i,
                null,
                '東京都',
                'place' . $i,
                0.1,
                0.2,
                new \DateTime('2020-01-01 10:00:00'),
                new \DateTime('2020-01-01 12:00:00'),
                $i,
                $i,
                $i,
                $i,
                'owner_nickname' . $i,
                'owner_display_name' . $i,
                $i,
                new \DateTime(),
                $types
            );
        }
        return $events;
    }
}
