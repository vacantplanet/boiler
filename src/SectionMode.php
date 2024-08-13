<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

enum SectionMode
{
    case Assign;

    case Append;

    case Prepend;

    case Closed;
}
