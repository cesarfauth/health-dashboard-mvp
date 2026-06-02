<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\BiomarkerInputDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHealthRecordRequest;
use App\Http\Resources\HealthRecordResource;
use App\Http\Resources\TrendAnalysisResource;
use App\Services\HealthRecordService;
use App\Services\TrendAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Thin HTTP layer: validates (FormRequest), delegates to the Service, and
 * serializes through Resources. No business logic lives here.
 */
class HealthRecordController extends Controller
{
    public function __construct(
        private readonly HealthRecordService $service,
        private readonly TrendAnalysisService $trends,
    ) {}

    /**
     * GET /api/health-records — recent history for the (placeholder) user.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->integer('limit', config('health.history_limit'));
        $records = $this->service->history($this->userId(), $limit);

        return HealthRecordResource::collection($records)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * POST /api/health-records — create a reading + AI snapshot analysis.
     */
    public function store(StoreHealthRecordRequest $request): JsonResponse
    {
        $input = BiomarkerInputDTO::fromArray($request->validated(), $this->userId());

        $record = $this->service->createWithAnalysis($input);

        return (new HealthRecordResource($record))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * GET /api/health-records/{id} — a single record with its recommendation.
     */
    public function show(int $id): JsonResponse
    {
        $record = $this->service->find($id, $this->userId());

        abort_if($record === null, Response::HTTP_NOT_FOUND, 'Health record not found.');

        return (new HealthRecordResource($record))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * POST /api/health-records/{id}/trend-analysis — temporal trend (differential).
     */
    public function trendAnalysis(int $id): JsonResponse
    {
        $record = $this->service->find($id, $this->userId());

        abort_if($record === null, Response::HTTP_NOT_FOUND, 'Health record not found.');

        $result = $this->trends->analyzeForRecord($record, $this->userId());

        return (new TrendAnalysisResource($result))
            ->response()
            ->setStatusCode(
                $result->sufficient ? Response::HTTP_CREATED : Response::HTTP_OK,
            );
    }

    private function userId(): int
    {
        return (int) config('health.default_user_id');
    }
}
