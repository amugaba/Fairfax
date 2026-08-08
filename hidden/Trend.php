<?php

/**
 * A Trend is a collection of variables, one for each year, that can be used to create a trend line graph.
 */
class Trend
{
    public string $name;
    public array $variablesByYear = [];

    const PRE_2024 = "2024"; //2024 and earlier
    const POST_2025 = "2025"; //2025 and later

    function __construct(string $trendName)
    {
        $this->name = $trendName;
    }

    /**
     * Convert an array of variables into a map by year.
     * @param Variable[] $variables
     */
    public function addVariables(array $variables): void
    {
        foreach ($variables as $variable) {
            $this->variablesByYear[$variable->year]  = $variable;
        }
    }
}