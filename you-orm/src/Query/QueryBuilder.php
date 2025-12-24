<?php

namespace YouOrm\Query;

use PDO;
use PDOStatement;

use YouOrm\Query\Grammar\GrammarInterface;
use YouOrm\Query\Grammar\MySqlGrammar;
use YouOrm\Query\Grammar\PostgreSqlGrammar;
use YouOrm\Query\Grammar\SqliteGrammar;
use YouOrm\Query\Grammar\SqlServerGrammar;

/**
 * Class QueryBuilder
 * Un constructeur de requêtes SQL fluide et expressif.
 *
 * Cette classe permet de construire des requêtes SQL de manière programmatique
 * tout en assurant la sécurité via des requêtes paramétrées.
 *
 * @package YouOrm\Query
 * @author Hamza Hajjaji
 */
class QueryBuilder
{
    //--- Constants ---
    public const string JOIN_INNER = 'INNER';
    public const string JOIN_LEFT = 'LEFT';
    public const string JOIN_RIGHT = 'RIGHT';
    public const string JOIN_FULL = 'FULL';

    /** @var PDO Instance de connexion à la base de données */
    private PDO $pdo;

    /** @var array Liste des champs à sélectionner */
    private array $selects = ['*'];

    /** @var string Table source de la requête */
    private string $from = '';

    /** @var array Liste des jointures */
    private array $joins = [];

    /** @var array Liste des clauses WHERE */
    private array $wheres = [];

    /** @var array Paramètres pour la requête préparée */
    private array $parameters = [];

    /** @var int|null Limite du nombre de résultats */
    private ?int $limit = null;

    /** @var int|null Décalage des résultats (OFFSET) */
    private ?int $offset = null;

    /** @var array Liste des clauses ORDER BY */
    private array $orderBy = [];

    /** @var array Liste des clauses GROUP BY */
    private array $groups = [];

    /** @var array Liste des clauses HAVING */
    private array $havings = [];

    /** @var GrammarInterface Grammaire SQL pour le SGBD actuel */
    private GrammarInterface $grammar;

    /**
     * Initialise une nouvelle instance du QueryBuilder.
     *
     * @param PDO $pdo L'instance de connexion PDO.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->detectGrammar();
    }

    /**
     * Détecte et instancie la grammaire appropriée selon le driver PDO.
     */
    private function detectGrammar(): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $this->grammar = match ($driver) {
            'pgsql' => new PostgreSqlGrammar(),
            'sqlsrv' => new SqlServerGrammar(),
            'sqlite' => new SqliteGrammar(),
            default => new MySqlGrammar(),
        };
    }

    /**
     * Définit manuellement une grammaire.
     *
     * @param GrammarInterface $grammar
     * @return self
     */
    public function setGrammar(GrammarInterface $grammar): self
    {
        $this->grammar = $grammar;
        return $this;
    }

    /**
     * Retourne la grammaire utilisée.
     *
     * @return GrammarInterface
     */
    public function getGrammar(): GrammarInterface
    {
        return $this->grammar;
    }

    /**
     * Définit les champs à sélectionner.
     *
     * @param string ...$fields Les noms des champs ou expressions SQL.
     * @return self
     */
    public function select(string ...$fields): self
    {
        $this->selects = $fields;
        return $this;
    }

    /**
     * Définit la table source de la requête.
     *
     * @param string $table Le nom de la table.
     * @param string|null $alias Un alias facultatif pour la table.
     * @return self
     */
    public function from(string $table, ?string $alias = null): self
    {
        $this->from = $alias ? "$table AS $alias" : $table;
        return $this;
    }

    /**
     * Ajoute une jointure à la requête.
     *
     * @param string $table La table à joindre.
     * @param string $condition La condition de jointure (ex: "u.id = p.user_id").
     * @param string $type Le type de jointure (INNER, LEFT, RIGHT).
     * @param string|null $alias Un alias pour la table jointe.
     * @return self
     */
    public function join(string $table, string $condition, string $type = self::JOIN_INNER, ?string $alias = null): self
    {
        $joinedTable = $alias ? "$table AS $alias" : $table;
        $this->joins[] = "$type JOIN $joinedTable ON $condition";
        return $this;
    }

    /**
     * Ajoute une jointure gauche (LEFT JOIN).
     *
     * @param string $table
     * @param string $condition
     * @param string|null $alias
     * @return self
     */
    public function leftJoin(string $table, string $condition, ?string $alias = null): self
    {
        return $this->join($table, $condition, self::JOIN_LEFT, $alias);
    }

    /**
     * Ajoute une jointure interne (INNER JOIN).
     *
     * @param string $table
     * @param string $condition
     * @param string|null $alias
     * @return self
     */
    public function innerJoin(string $table, string $condition, ?string $alias = null): self
    {
        return $this->join($table, $condition, self::JOIN_INNER, $alias);
    }

    /**
     * Ajoute une jointure droite (RIGHT JOIN).
     *
     * @param string $table
     * @param string $condition
     * @param string|null $alias
     * @return self
     */
    public function rightJoin(string $table, string $condition, ?string $alias = null): self
    {
        return $this->join($table, $condition, self::JOIN_RIGHT, $alias);
    }

    /**
     * Ajoute une clause WHERE simple (alias de andWhere).
     *
     * @param string $condition La condition SQL (ex: "id = :id").
     * @return self
     */
    public function where(string $condition): self
    {
        return $this->andWhere($condition);
    }

    /**
     * Ajoute une clause WHERE avec un opérateur AND.
     *
     * @param string $condition
     * @return self
     */
    public function andWhere(string $condition): self
    {
        $this->wheres[] = ['type' => 'AND', 'condition' => $condition];
        return $this;
    }

    /**
     * Ajoute une clause WHERE avec un opérateur OR.
     *
     * @param string $condition
     * @return self
     */
    public function orWhere(string $condition): self
    {
        $this->wheres[] = ['type' => 'OR', 'condition' => $condition];
        return $this;
    }

    /**
     * Définit la valeur d'un paramètre.
     *
     * @param string $key Le nom du paramètre (ex: "id").
     * @param mixed $value La valeur associée.
     * @return self
     */
    public function setParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Définit plusieurs paramètres à la fois.
     *
     * @param array $parameters Tableau associatif [clé => valeur].
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }
        return $this;
    }

    /**
     * Ajoute une clause de tri.
     *
     * @param string $sort Le champ à trier.
     * @param string $order La direction (ASC ou DESC).
     * @return self
     */
    public function orderBy(string $sort, string $order = 'ASC'): self
    {
        $this->orderBy[] = "$sort $order";
        return $this;
    }

    /**
     * Ajoute une clause de regroupement (GROUP BY).
     *
     * @param string ...$fields
     * @return self
     */
    public function groupBy(string ...$fields): self
    {
        $this->groups = array_merge($this->groups, $fields);
        return $this;
    }

    /**
     * Ajoute une clause HAVING simple.
     *
     * @param string $condition
     * @return self
     */
    public function having(string $condition): self
    {
        return $this->andHaving($condition);
    }

    /**
     * Ajoute une clause HAVING avec un opérateur AND.
     *
     * @param string $condition
     * @return self
     */
    public function andHaving(string $condition): self
    {
        $this->havings[] = ['type' => 'AND', 'condition' => $condition];
        return $this;
    }

    /**
     * Ajoute une clause HAVING avec un opérateur OR.
     *
     * @param string $condition
     * @return self
     */
    public function orHaving(string $condition): self
    {
        $this->havings[] = ['type' => 'OR', 'condition' => $condition];
        return $this;
    }

    /**
     * Définit le nombre maximum de résultats.
     *
     * @param int $limit
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Définit le point de départ des résultats (OFFSET).
     *
     * @param int $offset
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Génère la requête SQL finale à partir des éléments configurés.
     *
     * @return string La requête SQL.
     */
    public function getQuery(): string
    {
        return $this->grammar->compileSelect(
            $this->selects,
            $this->from,
            $this->joins,
            $this->wheres,
            $this->groups,
            $this->havings,
            $this->orderBy,
            $this->limit,
            $this->offset
        );
    }

    /**
     * Exécute la requête SQL et retourne l'objet statement.
     *
     * @return PDOStatement Le résultat de l'exécution.
     */
    public function execute(): PDOStatement
    {
        $sql = $this->getQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->parameters);
        return $stmt;
    }

    /**
     * Exécute la requête et retourne tous les résultats sous forme de tableau.
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Exécute la requête et retourne le premier résultat ou null.
     *
     * @return array|null
     */
    public function getSingleResult(): ?array
    {
        $result = $this->execute()->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Exécute la requête et retourne le nombre de lignes affectées.
     *
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->execute()->rowCount();
    }

    /**
     * Réinitialise le constructeur de requête pour une nouvelle utilisation.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->selects = ['*'];
        $this->from = '';
        $this->joins = [];
        $this->wheres = [];
        $this->parameters = [];
        $this->limit = null;
        $this->offset = null;
        $this->orderBy = [];
        $this->groups = [];
        $this->havings = [];
        return $this;
    }
}
