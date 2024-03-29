#!/usr/bin/env sh

#
# JBZoo Toolbox - Csv-Blueprint.
#
# This file is part of the JBZoo Toolbox project.
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
# @license    MIT
# @copyright  Copyright (C) JBZoo.com, All rights reserved.
# @see        https://github.com/JBZoo/Csv-Blueprint
#

echo "----"
php ./tests/Benchmarks/bench.php --columns=$BENCH_COLS --rows=0 --add-header  --ansi -vv
php ./tests/Benchmarks/bench.php --columns=$BENCH_COLS --rows=$BENCH_ROWS_SRC --ansi -vv

echo "----"
echo "Source file size : $(du -h ./build/bench/${BENCH_COLS}_${BENCH_ROWS_SRC}.csv)"
echo "Source rows count: $(wc -l ./build/bench/${BENCH_COLS}_${BENCH_ROWS_SRC}.csv)"

cat ./build/bench/${BENCH_COLS}_header.csv > $BENCH_CSV_PATH
for i in {1..1000}; do
    cat ./build/bench/${BENCH_COLS}_${BENCH_ROWS_SRC}.csv >> $BENCH_CSV_PATH
done

echo "----"
echo $BENCH_CSV_PATH
head $BENCH_CSV_PATH

echo "----"
echo "File size : $(du -h $BENCH_CSV_PATH)"
echo "Rows count: $(wc -l $BENCH_CSV_PATH)"

echo "----"
echo "Done!"
