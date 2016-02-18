<?php
namespace CodeBuffer;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;


/**
 * A buffer class can generate multiple-linke block code.
 *
 * It uses line-based unit to generate code, however the added
 * element doesn't have to be string, it can be anything
 * stringify-able objects (support __toString() method) or
 * implemented with Renderable interface.
 */
class Buffer implements IteratorAggregate, ArrayAccess
{
    public $lines = array();

    public $args = array();

    public function __construct(array $lines = array())
    {
        $this->lines = $lines;
    }

    /**
     * The default indent level.
     */
    public $indentLevel = 0;

    public function setDefaultArguments(array $args)
    {
        $this->args = $args;
        return $this;
    }

    public function setLines(array $lines)
    {
        $this->lines = $lines;
        return $this;
    }

    /**
     * Allow text can be set with array
     */
    public function setBody($text)
    {
        if (is_string($text)) {
            $this->lines = explode("\n", $text);
        } elseif (is_array($text)) {
            $this->lines = $text;
        } else {
            throw new InvalidArgumentTypeException('Invalid body type', $text, array('string', 'array'));
        }
    }

    public function appendRenderable(Renderable $obj)
    {
        $this->lines[] = $obj;
    }

    public function appendLine($line)
    {
        $this->lines[] = $line;
    }

    public function increaseIndentLevel()
    {
        $this->indentLevel++;
        return $this;
    }

    public function decreaseIndentLevel()
    {
        $this->indentLevel--;
        return $this;
    }

    public function indent()
    {
        $this->indentLevel++;
        return $this;
    }

    public function unindent()
    {
        $this->indentLevel--;
        return $this;
    }

    public function splice($from, $length, array $replacement = array())
    {
        return array_splice($this->lines, $from, $length, $replacement);
    }

    public function setIndentLevel($indent)
    {
        $this->indentLevel = $indent;
    }

    public function render()
    {
        $tab = Indenter::indent($this->indentLevel);
        $body = '';
        foreach ($this->lines as $line) {
            if (is_string($line)) {
                $body .= $tab . $line . "\n";
            } else if (is_object($line)) {

                if (method_exists($line,'__toString')) {

                    $body .= $line->__toString() . "\n";

                } else {
                    throw new Exception('Object does not support __toString method');
                }

            } else {
                throw new Exception('Unsupported line object type');
            }
        }
        return $body;
    }

    // ============ interface ArrayAggregator implementation =============
    public function getIterator()
    {
        return new ArrayIterator($this->lines);
    }

    // ============ interface ArrayAccess implementation =============
    public function offsetSet($key, $value)
    {
        if ($key) {
            $this->lines[$key] = $value;
        } else {
            $this->lines[] = $value;
        }
    }

    public function offsetExists($key)
    {
        return isset($this->lines[$key]);
    }

    public function offsetGet($key)
    {
        return $this->lines[$key];
    }

    public function offsetUnset($key)
    {
        unset($this->lines[$key]);
    }
}
