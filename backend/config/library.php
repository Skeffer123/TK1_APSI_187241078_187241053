<?php

return [
    'borrow_days' => (int) env('LIBRARY_DEFAULT_BORROW_DAYS', 7),
    'fine_per_day' => (int) env('LIBRARY_TARIF_DENDA_PER_HARI', 2000),
];
