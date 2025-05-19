<?php

namespace App\View\Components\Sidebar;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use function Termwind\style;

class Links extends Component
{

    public string $title, $route, $icon, $active;
    public function __construct($title, $route, $icon)
    {
        $this->title = $title;
        $this->route = $route;
        $this->icon = $icon;
        $basepath = $this->generatePath($route);
        $this->active = request()->routeIs($basepath) ? 'bg-blue-200 text-black' : '';
    }


    public function generatePath($route)
    {
        if (str_contains($route, '.')) {
            $path = explode('.', $route);
            return $path[0] . '.*';
        } else {
            return $route;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.sidebar.links');
    }
}
