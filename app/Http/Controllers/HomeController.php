<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResourceCardResource;
use App\Models\Resource;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    /**
     * Display the front page resource feed.
     */
    public function __invoke(): Response
    {
        $latestResources = Resource::query()
            ->latest('published_at')
            ->latest('id')
            ->limit(4)
            ->get();

        return Inertia::render('home', [
            'canRegister' => Features::enabled(Features::registration()),
            'latestResources' => ResourceCardResource::collection($latestResources)->resolve(),
            'resourcesIndexUrl' => route('resources.index'),
        ]);
    }
}
