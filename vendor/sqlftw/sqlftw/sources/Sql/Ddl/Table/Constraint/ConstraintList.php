<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Constraint;

use function array_filter;

class ConstraintList
{

    /** @var array<string|int, ConstraintDefinition> ($name => $constraint) */
    private array $constraints = [];

    /** @var array<string|int, ConstraintDefinition> ($name => $constraint) */
    private array $droppedConstraints = [];

    /**
     * @param list<ConstraintDefinition> $constraints
     */
    public function __construct(array $constraints)
    {
        foreach ($constraints as $constraint) {
            $this->addConstraint($constraint);
        }
    }

    private function addConstraint(ConstraintDefinition $constraint): void
    {
        if ($constraint->getName() !== null) {
            $this->constraints[$constraint->getName()] = $constraint;
        } else {
            $this->constraints[] = $constraint;
        }
    }

    public function updateRenamedConstraint(ConstraintDefinition $renamedConstraint, ?string $newName = null): void
    {
        foreach ($this->constraints as $oldName => $constraint) {
            if ($constraint === $renamedConstraint) {
                unset($this->constraints[$oldName]);
                if ($newName !== null) {
                    $this->constraints[$newName] = $constraint;
                } else {
                    $this->constraints[] = $constraint;
                }
            }
        }
    }

    /**
     * @return array<string|int, ConstraintDefinition>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return array<string|int, ConstraintDefinition>
     */
    public function getDroppedConstraints(): array
    {
        return $this->droppedConstraints;
    }

    /**
     * @return array<string|int, ForeignKeyDefinition>
     */
    public function getForeignKeys(): array
    {
        /** @var list<ForeignKeyDefinition> $result */
        $result = array_filter($this->constraints, static function (ConstraintDefinition $constraint) {
            return $constraint->getBody() instanceof ForeignKeyDefinition;
        });

        return $result;
    }

    /**
     * @return array<string|int, ForeignKeyDefinition>
     */
    public function getDroppedForeignKeys(): array
    {
        /** @var list<ForeignKeyDefinition> $result */
        $result = array_filter($this->droppedConstraints, static function (ConstraintDefinition $constraint) {
            return $constraint->getBody() instanceof ForeignKeyDefinition;
        });

        return $result;
    }

}
