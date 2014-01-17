<?php
/*
 * Copyright (c) 2014 Michel Petit <petit.michel@gmail.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


namespace Malenki\Math;

/**
 * Matrix basic implementation.
 * 
 * @todo as a reminder: http://www.latp.univ-mrs.fr/~torresan/CalcMat/cours/node2.html
 *
 * @property-read $cols Amount of columns
 * @property-read $rows Amount of rows
 * @author Michel Petit <petit.michel@gmail.com> 
 * @license MIT
 */
class Matrix
{
    protected $arr = array();
    protected $size = null;



    public function __get($name)
    {
        if(in_array($name, array('cols', 'rows')))
        {
            return $this->size->$name;
        }
    }



    /**
     * Constructs new matrix giving its size.
     * 
     * @param integer $int_rows 
     * @param integer $int_cols 
     * @access public
     * @return Matrix
     */
    public function __construct($int_rows, $int_cols)
    {
        if(!is_numeric($int_cols) || !is_numeric($int_rows))
        {
            throw new \InvalidArgumentException('number of cols and rows must be integers.');
        }

        $int_cols = (integer) $int_cols;
        $int_rows = (integer) $int_rows;

        if($int_cols <= 0 || $int_rows <= 0)
        {
            throw new \InvalidArgumentException('Number of cols and rows must be positive not null integers.');
        }

        $this->size = new \stdClass();
        $this->size->cols = $int_cols;
        $this->size->rows = $int_rows;
    }



    /**
     * Puts all values into the matrix. 
     * 
     * Argument is a simple array of one dimension. If the matrix has 3 columns 
     * and 2 rows, the the argument must have 6 elements. Under the hood, the 
     * given array is split to fit the matrix.
     *
     * By the way, you can use rows and colunms in place of this method.
     *
     * @param array $arrAll 
     * @access public
     * @return Matrix
     */
    public function populate($arrAll)
    {
        $this->arr = array_chunk($arrAll, $this->size->cols);

        return $this;
    }



    public function get($int_i, $int_j)
    {
        if(!is_integer($int_i) || !is_integer($int_j))
        {
            throw new \InvalidArgumentException('Indices must be integers.');
        }

        if(
            $int_i >= $this->size->rows
            ||
            $int_j >= $this->size->cols
            ||
            $int_i < 0
            ||
            $int_j < 0
        )
        {
            throw new \InvalidArgumentException('Row’s index or column’s index does not exist!');
        }
        return $this->arr[$int_i][$int_j];
    }



    /**
     * Adds a row to populate step by step the matrix
     * 
     * @param array $arr_row Data to add
     * @access public
     * @return Matrix
     */
    public function addRow(array $arr_row)
    {
        if(count($this->arr) == $this->size->rows)
        {
            throw new \OutOfRangeException(sprintf('You cannot add another row! Max number of rows is %d', $this->size->rows));
        }

        if(count($arr_row) != $this->size->cols)
        {
            throw new \InvalidArgumentException('New row must have same amout of columns than defined into the size matrix');
        }

        $this->arr[] = $arr_row;

        return $this;
    }



    /**
     * Adds a column to populate step by step the matrix
     * 
     * @param array $arr_col Data to add
     * @access public
     * @return Matrix
     */
    public function addCol($arr_col)
    {
        if(isset($this->arr[0]) && (count($this->arr[0]) == $this->size->cols))
        {
            throw new \OutOfRangeException(sprintf('You cannot add another column! Max number of columns is %d', $this->size->cols));
        }

        if(count($arr_col) != $this->size->rows)
        {
            throw new \InvalidArgumentException('New column must have same amout of rows than previous columns.');
        }

        $arr_col = array_values($arr_col); //to be sure to have index 0, 1, 2…

        foreach($arr_col as $k => $v)
        {
            $this->arr[$k][] = $arr_col[$k];
        }


        return $this;
    }



    /**
     * Gets the row having the given index.
     * 
     * @param integer $int Row's index
     * @access public
     * @return mixed
     */
    public function getRow($int = 0)
    {
        if(!isset($this->arr[$int]))
        {
            throw new \OutOfRangeException('There is no line having this index.');
        }

        return $this->arr[$int];
    }



    /**
     * Gets the column having the given index.
     * 
     * @param integer $int column's index 
     * @access public
     * @return mixed
     */
    public function getCol($int = 0)
    {
        if($int >= $this->size->cols)
        {
            throw new \OutOfRangeException('There is not column having this index.');
        }

        $arr_out = array();

        foreach($this->arr as $row)
        {
            $arr_out[] = $row[$int];
        }

        return $arr_out;
    }



    /**
     * Tells whether the current matrix is square or not. 
     * 
     * @access public
     * @return boolean
     */
    public function isSquare()
    {
        return $this->size->cols == $this->size->rows;
    }



    /**
     * Tests whether the current matrix is the same as the given one.
     * 
     * @param Matrix $matrix 
     * @access public
     * @return boolean
     */
    public function sameSize($matrix)
    {
        return (
            $this->size->cols == $matrix->cols
            &&
            $this->size->rows == $matrix->rows
        );
    }



    /**
     * Tests whether the current matrix can be multiply with the given one.
     *
     * @param mixed $matrix Scalar, complex or matrix
     * @access public
     * @return boolean
     */
    public function multiplyAllow($matrix)
    {
        if(is_numeric($matrix))
        {
            return true;
        }
        
        if($matrix instanceof \Malenki\Math\Complex)
        {
            return true;
        }

        if($matrix instanceof \Malenki\Math\Matrix)
        {
            return $this->size->cols == $matrix->rows;
        }

        return false;
    }



    /**
     * Returns the transpose of the current matrix.
     * 
     * @access public
     * @return Matrix
     */
    public function transpose()
    {
        $out = new self($this->size->cols, $this->size->rows);

        foreach($this->arr as $row)
        {
            $out->addCol($row);
        }

        return $out;
    }



    /**
     * Adds the given matrix with the current one to give another new matrix. 
     * 
     * @param Matrix $matrix Matrix to add
     * @access public
     * @return Matrix
     */
    public function add($matrix)
    {
        if(!($matrix instanceof \Malenki\Math\Matrix))
        {
            throw new \InvalidArgumentException('Given argument must be an instance of \Malenki\Math\Matrix');
        }

        if(!$this->sameSize($matrix))
        {
            throw new \RuntimeException('Cannot adding given matrix: it has wrong size.');
        }

        $out = new self($this->size->rows, $this->size->cols);

        foreach($this->arr as $k => $v)
        {
            $arrOther = $matrix->getRow($k);
            $arrNew = array();

            foreach($v as $kk => $vv)
            {
                $arrNew[] = $arrOther[$kk] + $vv;
            }

            $out->addRow($arrNew);
        }

        return $out;
    }



    /**
     * Multiplies current matrix to another one or to a scalar. 
     * 
     * @param mixed $mix Number (Real or Complex) or Matrix
     * @access public
     * @return Matrix
     */
    public function multiply($mix)
    {
        if(!$this->multiplyAllow($mix))
        {
            throw new \RuntimeException('Invalid number or matrix has not right number of rows.');
        }


        if($mix instanceof \Malenki\Math\Matrix)
        {
            $out = new self($this->size->rows, $mix->cols);

            for($r = 0; $r < $this->size->rows; $r++)
            {
                $arrOutRow = array();

                for($c = 0; $c < $mix->cols; $c++)
                {
                    $arrCol = $mix->getCol($c);
                    $arrRow = $this->getRow($r);

                    $arrItem = array();
                    $hasComplex = false;

                    foreach($arrCol as $k => $v)
                    {
                        if($arrRow[$k] instanceof Complex)
                        {
                            $arrItem[] = $arrRow[$k]->multiply($v);
                            $hasComplex = true;
                        }
                        elseif($v instanceof Complex)
                        {
                            $arrItem[] = $v->multiply($arrRow[$k]);
                            $hasComplex = true;
                        }
                        else
                        {
                            $arrItem[] = $arrRow[$k] * $v;
                        }
                    }

                    if($hasComplex)
                    {
                        $sum = new Complex(0, 0);

                        foreach($arrItem as $item)
                        {
                            if(is_numeric($item))
                            {
                                $item = new Complex($item, 0);
                            }

                            $sum = $item->add($sum);
                        }

                        $arrOutRow[] = $sum;
                    }
                    else
                    {
                        $arrOutRow[] = array_sum($arrItem);
                    }
                }

                $out->addRow($arrOutRow);
            }

            return $out;
        }

        if(is_numeric($mix) || $mix instanceof Complex)
        {
            $out = new self($this->size->rows, $this->size->cols);

            for($r = 0; $r < $this->size->rows; $r++)
            {
                $arrRow = $this->getRow($r);

                foreach($arrRow as $k => $v)
                {
                    if(is_numeric($mix))
                    {
                        if($v instanceof Complex)
                        {
                            $arrRow[$k] = $v->multiply($mix);
                        }
                        else
                        {
                            $arrRow[$k] = $mix * $v;
                        }
                    }
                    else
                    {
                        $arrRow[$k] = $mix->multiply($v);
                    }
                }

                $out->addRow($arrRow);
            }

            return $out;
        }
    }


    public function cofactor()
    {
        $c = new self($this->size->rows, $this->size->cols);


        for($m = 0; $m < $this->size->rows; $m++)
        {
            $arr_row = array();

            for($n = 0; $n < $this->size->cols; $n++)
            {
                if($this->size->cols == 2)
                {
                    $arr_row[] = pow(-1, $m + $n) * $this->subMatrix($m, $n)->get(0,0);
                }
                else
                {
                    $arr_row[] = pow(-1, $m + $n) * $this->subMatrix($m, $n)->det();
                }
            }

            $c->addRow($arr_row);
        }

        return $c;
    }



    public function adjugate()
    {
        return $this->cofactor()->transpose();
    }



    public function inverse()
    {
        $det = $this->det();

        if($det == 0)
        {
            throw new \RuntimeException('Cannot get inverse matrix: determinant is nul!');
        }

        return $this->adjugate()->multiply(1 / $det);
    }



    /**
     * Gets sub matrix from current. 
     * 
     * This is usefull for determinant calculus, inverse, etc.
     *
     * @param integer $int_m Index of rows to ignore
     * @param integer $int_n Index of column to ignore
     * @access public
     * @return Matrix
     */
    public function subMatrix($int_m, $int_n)
    {
        $sm = new self($this->size->rows - 1, $this->size->cols - 1);

        foreach($this->arr as $m => $row)
        {
            if($m != $int_m)
            {
                $arr_row = array();

                foreach($row as $n => $v)
                {
                    if($n != $int_n)
                    {
                        $arr_row[] = $v;
                    }
                }

                $sm->addRow($arr_row);
            }
        }

        return $sm;
    }



    /**
     * Computes the determinant. 
     * 
     * @todo What about complex number?
     * @throw \RuntimeException If matrix is not square.
     * @access public
     * @return float
     */
    public function det()
    {
        if(!$this->isSquare())
        {
            throw new \RuntimeException('Cannot compute determinant of non square matrix!');
        }

        if($this->size->rows == 2)
        {
            return $this->get(0,0) * $this->get(1,1) - $this->get(0,1) * $this->get(1,0);
        }
        else
        {
            $int_out = 0;

            $arr_row = $this->arr[0];

            foreach($arr_row as $n => $v)
            {
                $int_out += pow(-1, $n + 2) * $v * $this->subMatrix(0, $n)->det();
            }

            return $int_out;
        }
    }
        


    /**
     * Gets all data of the current matrix as 2 dimensions array
     * 
     * @access public
     * @return array
     */
    public function getAll()
    {
        return $this->arr;
    }



    /**
     * Returns human readable matrix string like a pseudo table.
     * 
     * @access public
     * @return string
     */
    public function __toString()
    {
        $arr_out = array();
        $arr_col_width = array();

        foreach($this->arr as $row)
        {
            $arr_row = array();

            foreach($row as $k => $item)
            {
                $arr_row[] = (string) $item;

                $int_length = strlen($arr_row[count($arr_row) - 1]);

                if(
                    (isset($arr_col_width[$k]) && $arr_col_width[$k] < $int_length)
                    ||
                    !isset($arr_col_width[$k])
                )
                {
                    $arr_col_width[$k] = $int_length;
                }
            }

            $arr_out[] = $arr_row;
        }

        foreach($arr_out as $idx => $row)
        {
            $arr_row = array();

            foreach($row as $k => $item)
            {
                $arr_row[] = str_pad($item, $arr_col_width[$k], ' ' ,STR_PAD_LEFT);
            }

            $arr_out[$idx] = implode('  ', $arr_row);
        }


        return implode(PHP_EOL, $arr_out);
    }
}
