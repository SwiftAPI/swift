<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Exception;

use RuntimeException;
use Swift\DependencyInjection\Attributes\DI;
use Throwable;

/**
 * Class AccessDeniedException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class AccessDeniedException extends RuntimeException implements RequestExceptionInterface {

    protected $code = 403;

    private array $greetings = array(
        'Hey bitch',
        'Biaaatch',
        'Hey son of a seahorse',
        'Hey sweetie',
        'Hey snot-rag',
    );

    private array $messages = array(
        'You are validating my inherent mistrust of strangers',
        'It\'s called FUCK OFF and it\'s located over there',
        'With breath like that, you shouldn\'t say anything at all',
        'Your ass must be jealous of all the shit that comes out your mouth',
        'Your comebacks are the same just like the size of your dick; small and pedantic',
        'Fat Albert called, he wants his belly back',
        'You\'re so fat, you have the only car in town with stretch marks',
        'I\'d like to see things from your point of view but I can\'t seem to get my head that far up my ass',
        'You get 10 times more chicks than me? 10 times 0 is 0',
        'I\'d give a fuck, but I already gave it to your mother last night',
        'No, no, sweetie. You\'re not \'THE shit\'. You\'re a PIECE of shit. There\'s a world of difference',
        'Were you born an idiot or have you had to work at it?',
        'Your house is so nasty, I tripped over a rat, and a cockroach stole my wallet',
        'That\'s what happens to you when your pussy has been invaded more than Poland',
        'If someone shoved my head up your ass, I\'d live off of the air in your head',
        'Want some lip stick? I think you will need it when you kiss my ass',
        'You\'re so fat that you bleed chocolate milk',
        'Looks like you got into the gene pool when the lifeguard wasn\'t looking',
        'Do you have to leave so soon? I was just about to poison the tea',
        'Life is but a game, and let me tell you, you suck at it',
        'I\'d try being nicer if you try being smarter',

    );

    /**
     * AccessDeniedException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct( string $message = "", int $code = 0, Throwable $previous = null ) {
        if ( $code !== 0 ) {
            $this->code = $code;
        }

        if ( $message === '' ) {
            $message = 'Access denied: ' . $this->greetings[ random_int( min: 0, max: ( count( $this->greetings ) - 1 ) ) ] . ', ' . lcfirst( $this->messages[ random_int( min: 0, max: ( count( $this->messages ) - 1 ) ) ] );
        }

        parent::__construct( $message, $code, $previous );
    }


}