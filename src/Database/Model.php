<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Triangle\Engine\Database;

use Closure;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * @method static EloquentModel make($attributes = [])
 * @method static EloquentBuilder withGlobalScope($identifier, $scope)
 * @method static EloquentBuilder withoutGlobalScope($scope)
 * @method static EloquentBuilder withoutGlobalScopes($scopes = null)
 * @method static array removedScopes()
 * @method static EloquentBuilder whereKey($id)
 * @method static EloquentBuilder whereKeyNot($id)
 * @method static EloquentBuilder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static EloquentModel|null firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method static EloquentBuilder orWhere($column, $operator = null, $value = null)
 * @method static EloquentBuilder latest($column = null)
 * @method static EloquentBuilder oldest($column = null)
 * @method static EloquentCollection hydrate($items)
 * @method static EloquentCollection fromQuery($query, $bindings = [])
 * @method static EloquentModel|EloquentCollection|static[]|static|null find($id, $columns = [])
 * @method static EloquentCollection findMany($ids, $columns = [])
 * @method static EloquentModel|EloquentCollection|static|static[] findOrFail($id, $columns = [])
 * @method static EloquentModel|static findOrNew($id, $columns = [])
 * @method static EloquentModel|static firstOrNew($attributes = [], $values = [])
 * @method static EloquentModel|static firstOrCreate($attributes = [], $values = [])
 * @method static EloquentModel|static updateOrCreate($attributes, $values = [])
 * @method static EloquentModel|static firstOrFail($columns = [])
 * @method static EloquentModel|static|mixed firstOr($columns = [], $callback = null)
 * @method static EloquentModel sole($columns = [])
 * @method static mixed value($column)
 * @method static EloquentCollection[]|static[] get($columns = [])
 * @method static EloquentModel[]|static[] getModels($columns = [])
 * @method static array eagerLoadRelations($models)
 * @method static LazyCollection cursor()
 * @method static Collection pluck($column, $key = null)
 * @method static LengthAwarePaginator paginate($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Paginator simplePaginate($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static CursorPaginator cursorPaginate($perPage = null, $columns = [], $cursorName = 'cursor', $cursor = null)
 * @method static EloquentModel|$this create($attributes = [])
 * @method static EloquentModel|$this forceCreate($attributes)
 * @method static int upsert($values, $uniqueBy, $update = null)
 * @method static void onDelete($callback)
 * @method static static|mixed scopes($scopes)
 * @method static static applyScopes()
 * @method static EloquentBuilder without($relations)
 * @method static EloquentBuilder withOnly($relations)
 * @method static EloquentModel newModelInstance($attributes = [])
 * @method static EloquentBuilder withCasts($casts)
 * @method static QueryBuilder getQuery()
 * @method static EloquentBuilder setQuery($query)
 * @method static QueryBuilder toBase()
 * @method static array getEagerLoads()
 * @method static EloquentBuilder setEagerLoads($eagerLoad)
 * @method static EloquentModel getModel()
 * @method static EloquentBuilder setModel($model)
 * @method static Closure getMacro($name)
 * @method static bool hasMacro($name)
 * @method static Closure getGlobalMacro($name)
 * @method static bool hasGlobalMacro($name)
 * @method static static clone ()
 * @method static EloquentBuilder has($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
 * @method static EloquentBuilder orHas($relation, $operator = '>=', $count = 1)
 * @method static EloquentBuilder doesntHave($relation, $boolean = 'and', $callback = null)
 * @method static EloquentBuilder orDoesntHave($relation)
 * @method static EloquentBuilder whereHas($relation, $callback = null, $operator = '>=', $count = 1)
 * @method static EloquentBuilder orWhereHas($relation, $callback = null, $operator = '>=', $count = 1)
 * @method static EloquentBuilder whereDoesntHave($relation, $callback = null)
 * @method static EloquentBuilder orWhereDoesntHave($relation, $callback = null)
 * @method static EloquentBuilder hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
 * @method static EloquentBuilder orHasMorph($relation, $types, $operator = '>=', $count = 1)
 * @method static EloquentBuilder doesntHaveMorph($relation, $types, $boolean = 'and', $callback = null)
 * @method static EloquentBuilder orDoesntHaveMorph($relation, $types)
 * @method static EloquentBuilder whereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
 * @method static EloquentBuilder orWhereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
 * @method static EloquentBuilder whereDoesntHaveMorph($relation, $types, $callback = null)
 * @method static EloquentBuilder orWhereDoesntHaveMorph($relation, $types, $callback = null)
 * @method static EloquentBuilder withAggregate($relations, $column, $function = null)
 * @method static EloquentBuilder withCount($relations)
 * @method static EloquentBuilder withMax($relation, $column)
 * @method static EloquentBuilder withMin($relation, $column)
 * @method static EloquentBuilder withSum($relation, $column)
 * @method static EloquentBuilder withAvg($relation, $column)
 * @method static EloquentBuilder withExists($relation)
 * @method static EloquentBuilder mergeConstraintsFrom($from)
 * @method static Collection explain()
 * @method static bool chunk($count, $callback)
 * @method static Collection chunkMap($callback, $count = 1000)
 * @method static bool each($callback, $count = 1000)
 * @method static bool chunkById($count, $callback, $column = null, $alias = null)
 * @method static bool eachById($callback, $count = 1000, $column = null, $alias = null)
 * @method static LazyCollection lazy($chunkSize = 1000)
 * @method static LazyCollection lazyById($chunkSize = 1000, $column = null, $alias = null)
 * @method static EloquentModel|object|static|null first($columns = [])
 * @method static EloquentModel|object|null baseSole($columns = [])
 * @method static EloquentBuilder tap($callback)
 * @method static mixed when($value, $callback, $default = null)
 * @method static mixed unless($value, $callback, $default = null)
 * @method static QueryBuilder select($columns = [])
 * @method static QueryBuilder selectSub($query, $as)
 * @method static QueryBuilder selectRaw($expression, $bindings = [])
 * @method static QueryBuilder fromSub($query, $as)
 * @method static QueryBuilder fromRaw($expression, $bindings = [])
 * @method static QueryBuilder addSelect($column)
 * @method static QueryBuilder distinct()
 * @method static QueryBuilder from($table, $as = null)
 * @method static QueryBuilder join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static QueryBuilder joinWhere($table, $first, $operator, $second, $type = 'inner')
 * @method static QueryBuilder joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static QueryBuilder leftJoin($table, $first, $operator = null, $second = null)
 * @method static QueryBuilder leftJoinWhere($table, $first, $operator, $second)
 * @method static QueryBuilder leftJoinSub($query, $as, $first, $operator = null, $second = null)
 * @method static QueryBuilder rightJoin($table, $first, $operator = null, $second = null)
 * @method static QueryBuilder rightJoinWhere($table, $first, $operator, $second)
 * @method static QueryBuilder rightJoinSub($query, $as, $first, $operator = null, $second = null)
 * @method static QueryBuilder crossJoin($table, $first = null, $operator = null, $second = null)
 * @method static QueryBuilder crossJoinSub($query, $as)
 * @method static void mergeWheres($wheres, $bindings)
 * @method static array prepareValueAndOperator($value, $operator, $useDefault = false)
 * @method static QueryBuilder whereColumn($first, $operator = null, $second = null, $boolean = 'and')
 * @method static QueryBuilder orWhereColumn($first, $operator = null, $second = null)
 * @method static QueryBuilder whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method static QueryBuilder orWhereRaw($sql, $bindings = [])
 * @method static QueryBuilder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static QueryBuilder orWhereIn($column, $values)
 * @method static QueryBuilder whereNotIn($column, $values, $boolean = 'and')
 * @method static QueryBuilder orWhereNotIn($column, $values)
 * @method static QueryBuilder whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
 * @method static QueryBuilder orWhereIntegerInRaw($column, $values)
 * @method static QueryBuilder whereIntegerNotInRaw($column, $values, $boolean = 'and')
 * @method static QueryBuilder orWhereIntegerNotInRaw($column, $values)
 * @method static QueryBuilder whereNull($columns, $boolean = 'and', $not = false)
 * @method static QueryBuilder orWhereNull($column)
 * @method static QueryBuilder whereNotNull($columns, $boolean = 'and')
 * @method static QueryBuilder whereBetween($column, $values, $boolean = 'and', $not = false)
 * @method static QueryBuilder whereBetweenColumns($column, $values, $boolean = 'and', $not = false)
 * @method static QueryBuilder orWhereBetween($column, $values)
 * @method static QueryBuilder orWhereBetweenColumns($column, $values)
 * @method static QueryBuilder whereNotBetween($column, $values, $boolean = 'and')
 * @method static QueryBuilder whereNotBetweenColumns($column, $values, $boolean = 'and')
 * @method static QueryBuilder orWhereNotBetween($column, $values)
 * @method static QueryBuilder orWhereNotBetweenColumns($column, $values)
 * @method static QueryBuilder orWhereNotNull($column)
 * @method static QueryBuilder whereDate($column, $operator, $value = null, $boolean = 'and')
 * @method static QueryBuilder orWhereDate($column, $operator, $value = null)
 * @method static QueryBuilder whereTime($column, $operator, $value = null, $boolean = 'and')
 * @method static QueryBuilder orWhereTime($column, $operator, $value = null)
 * @method static QueryBuilder whereDay($column, $operator, $value = null, $boolean = 'and')
 * @method static QueryBuilder orWhereDay($column, $operator, $value = null)
 * @method static QueryBuilder whereMonth($column, $operator, $value = null, $boolean = 'and')
 * @method static QueryBuilder orWhereMonth($column, $operator, $value = null)
 * @method static QueryBuilder whereYear($column, $operator, $value = null, $boolean = 'and')
 * @method static QueryBuilder orWhereYear($column, $operator, $value = null)
 * @method static QueryBuilder whereNested($callback, $boolean = 'and')
 * @method static QueryBuilder forNestedWhere()
 * @method static QueryBuilder addNestedWhereQuery($query, $boolean = 'and')
 * @method static QueryBuilder whereExists($callback, $boolean = 'and', $not = false)
 * @method static QueryBuilder orWhereExists($callback, $not = false)
 * @method static QueryBuilder whereNotExists($callback, $boolean = 'and')
 * @method static QueryBuilder orWhereNotExists($callback)
 * @method static QueryBuilder addWhereExistsQuery($query, $boolean = 'and', $not = false)
 * @method static QueryBuilder whereRowValues($columns, $operator, $values, $boolean = 'and')
 * @method static QueryBuilder orWhereRowValues($columns, $operator, $values)
 * @method static QueryBuilder whereJsonContains($column, $value, $boolean = 'and', $not = false)
 * @method static QueryBuilder orWhereJsonContains($column, $value)
 * @method static QueryBuilder whereJsonDoesntContain($column, $value, $boolean = 'and')
 * @method static QueryBuilder orWhereJsonDoesntContain($column, $value)
 * @method static QueryBuilder whereJsonLength($column, $operator, $value = null, $boolean = 'and')
 * @method static QueryBuilder orWhereJsonLength($column, $operator, $value = null)
 * @method static QueryBuilder dynamicWhere($method, $parameters)
 * @method static QueryBuilder groupBy(...$groups)
 * @method static QueryBuilder groupByRaw($sql, $bindings = [])
 * @method static QueryBuilder having($column, $operator = null, $value = null, $boolean = 'and')
 * @method static QueryBuilder orHaving($column, $operator = null, $value = null)
 * @method static QueryBuilder havingBetween($column, $values, $boolean = 'and', $not = false)
 * @method static QueryBuilder havingRaw($sql, $bindings = [], $boolean = 'and')
 * @method static QueryBuilder orHavingRaw($sql, $bindings = [])
 * @method static QueryBuilder orderBy($column, $direction = 'asc')
 * @method static QueryBuilder orderByDesc($column)
 * @method static QueryBuilder inRandomOrder($seed = '')
 * @method static QueryBuilder orderByRaw($sql, $bindings = [])
 * @method static QueryBuilder skip($value)
 * @method static QueryBuilder offset($value)
 * @method static QueryBuilder take($value)
 * @method static QueryBuilder limit($value)
 * @method static QueryBuilder forPage($page, $perPage = 15)
 * @method static QueryBuilder forPageBeforeId($perPage = 15, $lastId = 0, $column = 'id')
 * @method static QueryBuilder forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
 * @method static QueryBuilder reorder($column = null, $direction = 'asc')
 * @method static QueryBuilder union($query, $all = false)
 * @method static QueryBuilder unionAll($query)
 * @method static QueryBuilder lock($value = true)
 * @method static QueryBuilder lockForUpdate()
 * @method static QueryBuilder sharedLock()
 * @method static QueryBuilder beforeQuery($callback)
 * @method static void applyBeforeQueryCallbacks()
 * @method static string toSql()
 * @method static int getCountForPagination($columns = [])
 * @method static string implode($column, $glue = '')
 * @method static bool exists()
 * @method static bool doesntExist()
 * @method static mixed existsOr($callback)
 * @method static mixed doesntExistOr($callback)
 * @method static int count($columns = '*')
 * @method static mixed min($column)
 * @method static mixed max($column)
 * @method static mixed sum($column)
 * @method static mixed avg($column)
 * @method static mixed average($column)
 * @method static mixed aggregate($function, $columns = [])
 * @method static float|int numericAggregate($function, $columns = [])
 * @method static bool insert($values)
 * @method static int insertOrIgnore($values)
 * @method static int insertGetId($values, $sequence = null)
 * @method static int insertUsing($columns, $query)
 * @method static bool updateOrInsert($attributes, $values = [])
 * @method static void truncate()
 * @method static Expression raw($value)
 * @method static array getBindings()
 * @method static array getRawBindings()
 * @method static QueryBuilder setBindings($bindings, $type = 'where')
 * @method static QueryBuilder addBinding($value, $type = 'where')
 * @method static QueryBuilder mergeBindings($query)
 * @method static array cleanBindings($bindings)
 * @method static Processor getProcessor()
 * @method static Grammar getGrammar()
 * @method static QueryBuilder useWritePdo()
 * @method static static cloneWithout($properties)
 * @method static static cloneWithoutBindings($except)
 * @method static QueryBuilder dump()
 * @method static void dd()
 * @method static void macro($name, $macro)
 * @method static void mixin($mixin, $replace = true)
 * @method static mixed macroCall($method, $parameters)
 */
class Model extends EloquentModel
{
}
