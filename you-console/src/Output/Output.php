<?php

declare(strict_types=1);

namespace YouConsole\Output;

/**
 * Classe de gestion de la sortie CLI.
 *
 * Permet d'écrire du texte formaté dans la console avec support
 * des styles ANSI et des tags de formatage.
 *
 * Supporte les tags suivants :
 * - <info>texte</info> : texte en cyan
 * - <error>texte</error> : texte en blanc sur fond rouge
 * - <comment>texte</comment> : texte en jaune
 * - <question>texte</question> : texte en noir sur fond cyan
 * - <success>texte</success> : texte en vert gras
 *
 * @package YouConsole\Output
 */
class Output
{
    /**
     * Écrit un message dans la sortie standard.
     *
     * @param string $message Message à afficher
     * @param bool $newline Ajouter un retour à la ligne
     */
    public function write(string $message, bool $newline = false): void
    {
        $formatted = $this->format($message);
        echo $formatted;

        if ($newline) {
            echo PHP_EOL;
        }
    }

    /**
     * Écrit un message avec retour à la ligne.
     *
     * @param string $message Message à afficher
     */
    public function writeln(string $message = ''): void
    {
        $this->write($message, true);
    }

    /**
     * Affiche un message de succès.
     *
     * @param string $message Message à afficher
     */
    public function success(string $message): void
    {
        $this->writeln('<success>' . $message . '</success>');
    }

    /**
     * Affiche un message d'erreur.
     *
     * @param string $message Message à afficher
     */
    public function error(string $message): void
    {
        $this->writeln('<error>' . $message . '</error>');
    }

    /**
     * Affiche un message d'information.
     *
     * @param string $message Message à afficher
     */
    public function info(string $message): void
    {
        $this->writeln('<info>' . $message . '</info>');
    }

    /**
     * Affiche un commentaire.
     *
     * @param string $message Message à afficher
     */
    public function comment(string $message): void
    {
        $this->writeln('<comment>' . $message . '</comment>');
    }

    /**
     * Affiche une question.
     *
     * @param string $message Message à afficher
     */
    public function question(string $message): void
    {
        $this->writeln('<question>' . $message . '</question>');
    }

    /**
     * Affiche un avertissement.
     *
     * @param string $message Message à afficher
     */
    public function warning(string $message): void
    {
        $this->writeln('<warning>' . $message . '</warning>');
    }


    /**
     * Formate un message en remplaçant les tags par des codes ANSI.
     *
     * @param string $message Message à formater
     * @return string Message formaté avec codes ANSI
     */
    private function format(string $message): string
    {
        // Remplacer les tags par les styles ANSI
        $patterns = [
            '/<info>(.*?)<\/info>/s',
            '/<error>(.*?)<\/error>/s',
            '/<comment>(.*?)<\/comment>/s',
            '/<question>(.*?)<\/question>/s',
            '/<success>(.*?)<\/success>/s',
            '/<warning>(.*?)<\/warning>/s',
        ];

        $replacements = [
            static fn($matches) => OutputStyle::apply('info', $matches[1]),
            static fn($matches) => OutputStyle::apply('error', $matches[1]),
            static fn($matches) => OutputStyle::apply('comment', $matches[1]),
            static fn($matches) => OutputStyle::apply('question', $matches[1]),
            static fn($matches) => OutputStyle::apply('success', $matches[1]),
            static fn($matches) => OutputStyle::apply('warning', $matches[1]),
        ];

        foreach ($patterns as $index => $pattern) {
            $message = preg_replace_callback($pattern, $replacements[$index], $message);
        }

        return $message;
    }
}
