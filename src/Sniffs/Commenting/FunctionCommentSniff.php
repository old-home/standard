<?php

declare(strict_types=1);

namespace GraywingsStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use const T_ABSTRACT;
use const T_ATTRIBUTE;
use const T_ATTRIBUTE_END;
use const T_DOC_COMMENT_CLOSE_TAG;
use const T_FINAL;
use const T_FUNCTION;
use const T_PRIVATE;
use const T_PROTECTED;
use const T_PUBLIC;
use const T_STATIC;
use const T_WHITESPACE;

/**
 * This sniff checks for the presence of a doc comment for functions.
 * It ensures that all functions, whether public, protected, or private,
 * are documented with a proper doc comment. This is essential for maintaining
 * code quality, readability, and providing clear documentation for developers.
 *
 * If a function is missing a doc comment, an error is reported.
 */
final class FunctionCommentSniff implements Sniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * This method specifies the types of tokens that the sniff will process.
     * In this case, it listens for function declarations.
     *
     * @return array<int> An array of token types to listen for.
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * Processes the tokens that this sniff is listening for.
     *
     * This method checks if a function has a preceding doc comment. If no doc
     * comment is found, an error is added to the PHP_CodeSniffer report.
     *
     * The method also skips over attributes, visibility modifiers (public,
     * protected, private), and other keywords (static, abstract, final) to
     * locate the doc comment.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack.
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $currentPtr = $stackPtr - 1;

        // Traverse backwards to skip attributes, visibility modifiers, and other keywords.
        while ($currentPtr > 0) {
            if ($tokens[$currentPtr]['code'] === T_ATTRIBUTE_END) {
                $currentPtr = $phpcsFile->findPrevious([T_ATTRIBUTE], $currentPtr);
            } elseif (
                $tokens[$currentPtr]['code'] !== T_WHITESPACE
                && $tokens[$currentPtr]['code'] !== T_PROTECTED
                && $tokens[$currentPtr]['code'] !== T_PRIVATE
                && $tokens[$currentPtr]['code'] !== T_PUBLIC
                && $tokens[$currentPtr]['code'] !== T_STATIC
                && $tokens[$currentPtr]['code'] !== T_ABSTRACT
                && $tokens[$currentPtr]['code'] !== T_FINAL
            ) {
                break;
            }

            $currentPtr--;
        }

        // Check if the preceding token is a doc comment.
        if ($tokens[$currentPtr]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            return;
        }

        // Add an error if no doc comment is found.
        $error = 'Missing function doc comment.';
        $phpcsFile->addError($error, $stackPtr, 'MissingFunctionComment');
    }
}
