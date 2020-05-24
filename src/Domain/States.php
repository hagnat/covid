<?php

declare(strict_types=1);

namespace App\Domain;

use ArrayIterator;

final class States extends ArrayIterator
{
    private static $englishInstance;

    private function __construct(State ...$states)
    {
        parent::__construct($states);
    }

    public static function english(): States
    {
        if (null !== static::$englishInstance) {
            return static::$englishInstance;
        }

        $data = [
            'North' => [
                ['Acre', 'AC', 'Acre (state)'],
                ['Amapá', 'AP'],
                ['Amazonas', 'AM', 'Amazonas (Brazilian state)'],
                ['Pará', 'PA'],
                ['Rondônia', 'RO'],
                ['Roraima', 'RR'],
                ['Tocantins', 'TO'],
            ],
            'Northeast' => [
                ['Alagoas', 'AL'],
                ['Bahia', 'BA'],
                ['Ceará', 'CE'],
                ['Maranhão', 'MA'],
                ['Paraíba', 'PB'],
                ['Pernambuco', 'PE'],
                ['Piauí', 'PI'],
                ['Rio Grande do Norte', 'RN'],
                ['Sergipe', 'SE'],
            ],
            'Central-West' => [
                ['Distrito Federal', 'DF', 'Federal District (Brazil)'],
                ['Goiás', 'GO'],
                ['Mato Grosso', 'MT'],
                ['Mato Grosso do Sul', 'MS'],
            ],
            'Southeast' => [
                ['Espírito Santo', 'ES'],
                ['Minas Gerais', 'MG'],
                ['Rio de Janeiro', 'RJ', 'Rio de Janeiro (state)'],
                ['São Paulo', 'SP', 'São Paulo (state)'],
            ],
            'South' => [
                ['Paraná', 'PR', 'Paraná (state)'],
                ['Rio Grande do Sul', 'RS'],
                ['Santa Catarina', 'SC', 'Santa Catarina (state)'],
            ],
        ];

        $states = [];
        foreach ($data as $region => $regionStates) {
            foreach ($regionStates as $stateData) {
                $name = array_shift($stateData);
                $code = array_shift($stateData);
                $wikipediaEntry = array_shift($stateData);
                $wikipediaFlag = array_shift($stateData);

                $states[] = new State($name, $code, Region::fromName($region), $wikipediaEntry, $wikipediaFlag);
            }
        }

        return static::$englishInstance = new static(...$states);
    }

    public function sort(): States
    {
        $states = clone $this;

        $states->uasort(function (State $a, State $b) {
            return $a->compare($b);
        });

        return $states;
    }

    public function findByCode(string $code): ?State
    {
        foreach ($this as $state) {
            if ($state->code() === $code) {
                return $state;
            }
        }

        return null;
    }

    public function northStates(): States
    {
        return $this->fromRegion(Region::north());
    }

    public function northeastStates(): States
    {
        return $this->fromRegion(Region::northeast());
    }

    public function centralWestStates(): States
    {
        return $this->fromRegion(Region::centralWest());
    }

    public function southeastStates(): States
    {
        return $this->fromRegion(Region::southeast());
    }

    public function southStates(): States
    {
        return $this->fromRegion(Region::south());
    }

    private function fromRegion(Region $region): States
    {
        return new static(...array_filter($this, function (State $state) use ($region) {
            return $state->fromRegion($region);
        }));
    }
}
