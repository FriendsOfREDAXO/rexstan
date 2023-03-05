<?php declare(strict_types = 1);

namespace SqlFtw\Sql\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\Statement;

class RepeatStatement extends Statement implements SqlSerializable
{

    /** @var list<Statement> */
    private array $statements;

    private RootNode $condition;

    private ?string $label;

    private bool $endLabel;

    /**
     * @param list<Statement> $statements
     */
    public function __construct(array $statements, RootNode $condition, ?string $label, bool $endLabel = false)
    {
        $this->statements = $statements;
        $this->condition = $condition;
        $this->label = $label;
        $this->endLabel = $endLabel;
    }

    /**
     * @return list<Statement>
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function getCondition(): RootNode
    {
        return $this->condition;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function endLabel(): bool
    {
        return $this->endLabel;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = '';
        if ($this->label !== null) {
            $result .= $formatter->formatName($this->label) . ': ';
        }
        $result .= "REPEAT\n";
        if ($this->statements !== []) {
            $result .= $formatter->formatSerializablesList($this->statements, ";\n") . ";\n";
        }
        $result .= "UNTIL " . $this->condition->serialize($formatter) . "\nEND REPEAT";
        if ($this->label !== null && $this->endLabel) {
            $result .= ' ' . $formatter->formatName($this->label);
        }

        return $result;
    }

}
