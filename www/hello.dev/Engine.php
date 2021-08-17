<?php

/**
 * Description of Engine.php
 *
 * @author puld
 */
class Engine
{
	const X_FIELD = 'X';
	const O_FIELD = 'O';
	const KEYMAP = [
		[7, 8, 9],
		[4, 5, 6],
		[1, 2, 3],
	];
	const WINNER_FIELDS = [
		[1, 2, 3],
		[4, 5, 6],
		[7, 8, 9],
		[1, 4, 7],
		[2, 5, 8],
		[3, 6, 9],
		[1, 5, 9],
		[3, 5, 7],
	];

	const STATE_RUN = 'run';
	const STATE_X_WIN = self::X_FIELD;
	const STATE_O_WIN = self::O_FIELD;
	const STATE_STALEMATE = 'stalemate';

	// all game field
	protected $gameField = array();

	// computer and player moves
	protected $playerMoves;

	protected $state = self::STATE_RUN;

	public function __construct()
	{
		$this->restore();

		$this->keyIndex = array();
		foreach (self::KEYMAP as $row => $cols)
		{
			foreach ($cols as $col => $key)
			{
				$this->keyIndex[$key] = array(
					'col' => $col,
					'row' => $row,
				);
			}
		}
	}

	public function __destruct()
	{
		$_SESSION['game']['gameField'] = $this->gameField;
		$_SESSION['game']['playerMoves'] = $this->playerMoves;
		$_SESSION['game']['state'] = $this->state;
	}

	/**
	 * @return array|mixed
	 */
	public function getGameField(): array
	{
		return $this->gameField;
	}

	/**
	 * @return string
	 */
	public function getState(): string
	{
		return $this->state;
	}

	public function restore(bool $reset = false)
	{
		if ($reset)
		{
			unset($_SESSION['game']['gameField']);
			unset($_SESSION['game']['playerMoves']);
			unset($_SESSION['game']['state']);
		}

		$this->gameField = $_SESSION['game']['gameField'] ?? [];
		$this->playerMoves = $_SESSION['game']['playerMoves'] ?? [self::X_FIELD => [], self::O_FIELD => []];
		$this->state = $_SESSION['game']['state'] ?? self::STATE_RUN;
	}

	public function applyUserMove(int $key): int
	{
		$computerMove = 0;
		if ($this->checkChoice($key))
		{
			$this->applyMove($key, self::X_FIELD);
			if ($this->state == self::STATE_RUN)
			{
				$computerMove = $this->applyComputerMove();
				if (is_int($computerMove))
				{
					$this->applyMove($computerMove, self::O_FIELD);
				}
			}
		}
		return $computerMove;

	}

	protected function applyMove(int $key, string $field)
	{
		$index = $this->keyIndex[$key];
		$this->gameField[$index['row']][$index['col']] = $field;

		$this->playerMoves[$field][] = $key;

		$this->checkWinner($field);
	}

	// check if user choice
	protected function checkChoice($userChoice)
	{
		return (in_array($userChoice, [1, 2, 3, 4, 5, 6, 7, 8, 9, 0])
				&& array_search($userChoice, array_merge($this->playerMoves[self::X_FIELD], $this->playerMoves[self::O_FIELD])) === false);
	}

	// make computer choice
	protected function applyComputerMove()
	{
		$movesLeft = array_diff([1, 2, 3, 4, 5, 6, 7, 8, 9], array_merge($this->playerMoves[self::X_FIELD], $this->playerMoves[self::O_FIELD]));

		// check if computer or player has 1 move to Win
		foreach (self::WINNER_FIELDS as $winnerCombination)
		{
			$availableMove = [];

			if (count(array_intersect($winnerCombination, $this->playerMoves[self::X_FIELD])) == 2)
			{
				$availableMove = array_diff($winnerCombination, array_intersect($winnerCombination, $this->playerMoves[self::X_FIELD]));
			}

			if (count(array_intersect($winnerCombination, $this->playerMoves[self::O_FIELD])) == 2)
			{
				$availableMove = array_diff($winnerCombination, array_intersect($winnerCombination, $this->playerMoves[self::O_FIELD]));
			}

			if (count(array_intersect($availableMove, $movesLeft)) != 0)
			{
				return array_pop($availableMove);
			}
		}

		// take place in center
		if (in_array(5, $movesLeft))
		{
			return 5;
		}


		shuffle($movesLeft);

		$computerMove = array_pop($movesLeft);

		return $computerMove;
	}

	// check for winner
	protected function checkWinner(string $field)
	{
		$result = self::STATE_RUN;
		foreach (self::WINNER_FIELDS as $winnerCombination)
		{
			if (count(array_intersect($winnerCombination, $this->playerMoves[$field])) == 3)
			{
				$result = $field;
			}
		}

		// no more moves
		if ((count($this->playerMoves[self::X_FIELD]) + count($this->playerMoves[self::O_FIELD])) == 9)
		{
			$result = self::STATE_STALEMATE;
		}

		$this->state = $result;
	}

}