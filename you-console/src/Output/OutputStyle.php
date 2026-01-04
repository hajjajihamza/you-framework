<?php

declare(strict_types=1);

namespace YouConsole\Output;

/**
 * Classe de gestion des styles ANSI pour la sortie CLI.
 *
 * Fournit des codes couleurs et styles ANSI pour formater le texte
 * affiché dans le terminal.
 *
 * @package YouConsole\Output
 */
class OutputStyle
{
    // Codes ANSI pour les couleurs
    private const string COLOR_BLACK = '30';
    private const string COLOR_RED = '31';
    private const string COLOR_GREEN = '32';
    private const string COLOR_YELLOW = '33';
    private const string COLOR_BLUE = '34';
    private const string COLOR_MAGENTA = '35';
    private const string COLOR_CYAN = '36';
    private const string COLOR_WHITE = '37';

    // Codes ANSI pour les couleurs de fond
    private const string BG_BLACK = '40';
    private const string BG_RED = '41';
    private const string BG_GREEN = '42';
    private const string BG_YELLOW = '43';
    private const string BG_BLUE = '44';
    private const string BG_MAGENTA = '45';
    private const string BG_CYAN = '46';
    private const string BG_WHITE = '47';

    // Codes ANSI pour les styles
    private const string STYLE_BOLD = '1';
    private const string STYLE_RESET = '0';

    /**
     * Styles prédéfinis pour les différents types de messages.
     *
     * @var array<string, array{fg: string, bg?: string, bold?: bool}>
     */
    private const array STYLES = [
        'info' => ['fg' => self::COLOR_CYAN],
        'error' => ['fg' => self::COLOR_WHITE, 'bg' => self::BG_RED, 'bold' => true],
        'comment' => ['fg' => self::COLOR_YELLOW],
        'question' => ['fg' => self::COLOR_BLACK, 'bg' => self::BG_CYAN],
        'success' => ['fg' => self::COLOR_GREEN, 'bold' => true],
        'warning' => ['fg' => self::COLOR_YELLOW, 'bg' => self::BG_RED, 'bold' => true],
    ];

    /**
     * Applique un style ANSI à un texte.
     *
     * @param string $style Nom du style (info, error, comment, question, success)
     * @param string $text Texte à styler
     * @return string Texte avec codes ANSI
     */
    public static function apply(string $style, string $text): string
    {
        if (!isset(self::STYLES[$style])) {
            return $text;
        }

        $styleConfig = self::STYLES[$style];
        $codes = [];

        if (isset($styleConfig['bold']) && $styleConfig['bold']) {
            $codes[] = self::STYLE_BOLD;
        }

        if (isset($styleConfig['fg'])) {
            $codes[] = $styleConfig['fg'];
        }

        if (isset($styleConfig['bg'])) {
            $codes[] = $styleConfig['bg'];
        }

        $startCode = "\033[" . implode(';', $codes) . "m";
        $endCode = "\033[" . self::STYLE_RESET . "m";

        return $startCode . $text . $endCode;
    }

    /**
     * Supprime tous les codes ANSI d'un texte.
     *
     * @param string $text Texte contenant des codes ANSI
     * @return string Texte sans codes ANSI
     */
    public static function strip(string $text): string
    {
        return preg_replace('/\033\[[0-9;]+m/', '', $text);
    }
}
