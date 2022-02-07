<?php

declare(strict_types=1);

namespace Flow\ETL\Formatter\ASCII;

/**
 * PHP ASCII Tables.
 *
 * This class will convert multi-dimensional arrays into ASCII Tables, and vice-versa.
 *
 * Slightly modified version of original Ascii_Table class, mostly with some features removed.
 *
 * @author    Phillip Gooch <phillip.gooch@gmail.com>
 * @copyright 2018 Phillip Gooch
 * @license  GPLv3
 *
 * @link      https://github.com/pgooch/PHP-Ascii-Tables
 */
final class ASCIITable
{
    /**
     * An array that contains the max character width of each column (not including buffer spacing).
     */
    private array $colWidths = [];

    /**
     * An array that contains the column types.
     */
    private array $colTypes = [];

    /**
     * This is the function that you will call to make the table. You must pass it at least the first variable.
     *
     * @param array<int, array<string, mixed>> $array A multi-dimensional array containing the data you want to build a table from
     *
     * @return string
     */
    public function makeTable(array $array) : string
    {
        $autoAlignCells = true;

        // First things first lets get the variable ready
        $table = '';
        $this->colWidths = [];
        $this->colTypes = [];

        // Modify the table to support any line breaks that might exist
        $modifiedArray = [];

        foreach ($array as $row => $rowData) {
            // This will break the cells up on line breaks and store them in $raw_array with the longest value for that column in $longest_cell
            $rowArray = [];
            $longestCell = 1;

            foreach ($rowData as $cell => $cellValue) {
                $cellValue = \explode("\n", $cellValue);
                $rowArray[$cell] = $cellValue;
                $longestCell = \max($longestCell, \count($cellValue));
            }

            // This will loop as many times as the longest, if there is a value it will use that, if not it will just give it an empty string
            for ($i = 0; $i < $longestCell; $i++) {
                $newRowTmp = [];

                foreach ($rowArray as $col => $colData) {
                    if (isset($colData[$i])) {
                        $newRowTmp[$col] = \trim($colData[$i]);
                    } else {
                        $newRowTmp[$col] = '';
                    }
                }
                $modifiedArray[] = $newRowTmp;
            }
        }

        // Finally we can call the fully modified array the array for future use
        $array = $modifiedArray;

        // Now we need to get some details prepared.
        $this->getColWidths($array);
        $this->getColTypes($array);

        // If we have a blank array then we don't need to output anything else
        if (isset($array[0])) {
            // Now we can output the header row, along with the divider rows around it
            $table .= $this->makeDivider();

            // Output the header row
            $table .= $this->makeHeaders($autoAlignCells);

            // Another divider line
            $table .= $this->makeDivider();

            // Add the table data in
            $table .= $this->makeRows($array, $autoAlignCells);

            // The final divider line.
            $table .= $this->makeDivider();
        }

        return $table;
    }

    /**
     * This method will set the $col_width variable with the longest value in each column.
     *
     * @param array $array the multi-dimensional array you are building the ASCII Table from
     */
    private function getColWidths(array $array) : void
    {
        // If we have some array data loop through each row, then through each cell
        if (isset($array[0])) {
            foreach (\array_keys($array[0]) as $col) {
                // Get the longest col value and compare with the col name to get the longest
                $this->colWidths[$col] = \max(\max(\array_map([$this, 'len'], $this->arrCol($array, $col))), $this->len($col));
            }
        }
    }

    /**
     * This method will set the $col_types variable with the type of value in each column.
     *
     * @param array $array the multi-dimensional array you are building the ASCII Table from
     */
    private function getColTypes(array $array) : void
    {
        // If we have some array data loop through each row, then through each cell
        if (isset($array[0])) {
            // Parse each col and each row to get the column type
            foreach (\array_keys($array[0]) as $col) {
                foreach ($array as $i => $row) {
                    if (\trim($row[$col]) != '') {
                        if (!isset($this->colTypes[$col])) {
                            $this->colTypes[$col] = \is_numeric($row[$col]) ? 'numeric' : 'string';
                        } else {
                            if ($this->colTypes[$col] == 'numeric') {
                                $this->colTypes[$col] = \is_numeric($row[$col]) ? 'numeric' : 'string';
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * This is an array_column shim, it will use the PHP array_column function if there is one, otherwise it will do the same thing the old way.
     *
     * @param array $array the multi-dimensional array you are building the ASCII Table from
     * @param string $col a table's key (column)
     *
     * @return array an array containing all values of a column
     */
    private function arrCol(array $array, string $col)
    {
        if (\is_callable('array_column')) {
            $return = \array_column($array, $col);
        } else {
            $return = [];

            foreach ($array as $n => $dat) {
                if (isset($dat[$col])) {
                    $return[] = $dat[$col];
                }
            }
        }

        return $return;
    }

    /**
     * This will use the data in the $col_width var to make a divider line.
     *
     * @return string a table's divider
     */
    private function makeDivider() : string
    {
        // were going to start with a simple union piece
        $divider = '+';

        // Loop through the table, adding lines of the appropriate length (remembering the +2 for the spacers), and a union piece at the end
        foreach ($this->colWidths as $col => $length) {
            $divider .= \str_repeat('-', $length + 2) . '+';
        }

        // return it
        return $divider . PHP_EOL;
    }

    /**
     * This will look through the $col_widths array and make a column header for each one.
     *
     * @param bool $autoalignCells if True, columns with numeric data types will be aligned to the right of the cell
     *
     * @return string the row of the table header
     */
    private function makeHeaders(bool $autoalignCells) : string
    {
        // This time were going to start with a simple bar;
        $row = '|';

        // Loop though the col widths, adding the cleaned title and needed padding
        foreach ($this->colWidths as $col => $length) {
            // Add title
            $alignment = $autoalignCells && isset($this->colTypes[$col]) && $this->colTypes[$col] == 'numeric' ? STR_PAD_LEFT : STR_PAD_RIGHT;
            $row .= ' ' . \str_pad($col, $this->colWidths[$col], ' ', $alignment) . ' ';

            // Add the right hand bar
            $row .= '|';
        }

        // Return the row
        return $row . PHP_EOL;
    }

    /**
     * This makes the actual table rows.
     *
     * @param array $array the multi-dimensional array you are building the ASCII Table from
     * @param bool $autoalignCells if True, column values with numeric data types will be aligned to the right of the cell
     *
     * @return string the rows of the table
     */
    private function makeRows(array $array, bool $autoalignCells) : string
    {
        // Just prep the variable
        $rows = '';

        // Loop through rows
        foreach ($array as $n => $data) {
            // Again were going to start with a simple bar
            $rows .= '|';

            // Loop through the columns
            foreach ($data as $col => $value) {
                // Add the value to the table
                $alignment = $autoalignCells && isset($this->colTypes[$col]) && $this->colTypes[$col] == 'numeric' ? STR_PAD_LEFT : STR_PAD_RIGHT;
                $rows .= ' ' . \str_pad($value, $this->colWidths[$col], ' ', $alignment) . ' ';

                // Add the right hand bar
                $rows .= '|';
            }

            // Add the row divider
            $rows .= PHP_EOL;
        }

        // Return the row
        return $rows;
    }

    /**
     * This function will use the mb_strlen if available or strlen.
     *
     * @param string $colValue the string that be need to be counted
     *
     * @return int returns a lenght of string using mb_strlen or strlen
     */
    private static function len(string $colValue) : int
    {
        return \extension_loaded('mbstring') ? \mb_strlen($colValue) : \strlen($colValue);
    }
}
