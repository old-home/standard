<?php

declare(strict_types=1);

namespace GraywingsStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use const T_ATTRIBUTE;
use const T_ATTRIBUTE_END;
use const T_CLASS;
use const T_DOC_COMMENT_CLOSE_TAG;
use const T_ENUM;
use const T_FINAL;
use const T_INTERFACE;
use const T_READONLY;
use const T_TRAIT;
use const T_WHITESPACE;

/**
 * This sniff checks for the presence of a doc comment for classes, interfaces,
 * traits, and enums. It ensures that all class-like structures are documented
 * with a proper doc comment, which is essential for maintaining code quality
 * and readability.
 *
 * If a class-like structure is missing a doc comment, an error is reported.
 */
final class ClassCommentSniff implements Sniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * This method specifies the types of tokens that the sniff will process.
     * In this case, it listens for class, interface, trait, and enum declarations.
     *
     * @return array<int> An array of token types to listen for.
     */
    public function register(): array
    {
        return [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM];
    }

    /**
     * Processes the tokens that this sniff is listening for.
     *
     * This method checks if the class-like structure (class, interface, trait, or enum)
     * has a preceding doc comment. If no doc comment is found, an error is added to
     * the PHP_CodeSniffer report.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack.
     */
    public function process(
        File $phpcsFile,
        $stackPtr,
    ): void {
        $tokens = $phpcsFile->getTokens();

        $currentPtr = $stackPtr - 1;

        // Traverse backwards to skip attributes, whitespace, and final keywords.
        while ($currentPtr > 0) {
            if ($tokens[$currentPtr]['code'] === T_ATTRIBUTE_END) {
                $currentPtr = $phpcsFile->findPrevious([T_ATTRIBUTE], $currentPtr);
            } elseif (
                $tokens[$currentPtr]['code'] !== T_WHITESPACE
                && $tokens[$currentPtr]['code'] !== T_READONLY
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
        $error = 'Missing class doc comment.';
        $phpcsFile->addError($error, $stackPtr, 'MissingClassComment');
    }
}
