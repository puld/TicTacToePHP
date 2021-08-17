<?php
/** @var Controller $this */
$engine = $this->engine;
?>

<html>
<head>
	<style>
        button.block, div.block {
            width: 50px;
            height: 50px;
        }

        div.block {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .center {
            top: 50%;
            left: 50%;
            position: absolute;
            margin-left: -80px;
            margin-top: -80px;
        }

	</style>
</head>
<body>

<div class="center">
	<table>
		<?php $gameField = $engine->getGameField(); ?>
		<?php foreach (Engine::KEYMAP as $row => $cols): ?>
			<tr>
				<?php foreach ($cols as $col => $key): ?>
					<td>
						<?php if ($gameField[$row][$col] ?? false) : ?>
							<div class="block"><?php echo $gameField[$row][$col]; ?></div>
						<?php else: ?>
							<button class="block" id="b_<?php echo $key; ?>" value="" onclick="move(<?php echo $key; ?>)"/>
						<?php endif; ?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</table>
	<div>
		<span id="result"></span> <a href="index.php?route=Reset">Начать заново</a>
	</div>
</div>

<script>

	gameEnd = false;

	function updateStatus(status) {
		let endMessage = null;
		switch (status) {
			case '<?php echo Engine::STATE_O_WIN; ?>':
				endMessage = 'Нолики выйграли!';
				break;
			case '<?php echo Engine::STATE_X_WIN; ?>':
				endMessage = 'Крестики выйграли!';
				break;
			case '<?php echo Engine::STATE_STALEMATE; ?>':
				endMessage = 'Ничья. Игра окончена.';
				break;
		}

		if (endMessage) {
			gameEnd = true;
			let spanResult = document.getElementById('result');
			spanResult.textContent = endMessage;
		}
	}

	function move(key) {

		if (gameEnd) {
			return;
		}

		fetch("index.php?route=Move&key=" + key, {
			method: "GET",
			headers: {"content-type": "application/x-www-form-urlencoded"}
		}).then(response => {
			if (response.status !== 200) {

				return Promise.reject();
			}
			return response.text()
		}).then(json => {
			const data = JSON.parse(json);

			let buttonUser = document.getElementById('b_' + key);
			let spanUser = document.createElement("div");
			spanUser.textContent = "<?php echo Engine::X_FIELD; ?>";
			spanUser.setAttribute("class", "block");
			buttonUser.parentNode.replaceChild(spanUser, buttonUser);

			if (data.computerMove) {
				let buttonComputer = document.getElementById('b_' + data.computerMove);
				let spanComputer = document.createElement("div");
				spanComputer.textContent = "<?php echo Engine::O_FIELD; ?>";
				spanComputer.setAttribute("class", "block");
				buttonComputer.parentNode.replaceChild(spanComputer, buttonComputer);
			}

			updateStatus(data.status);

		}).catch((e) => console.log('ошибка', e));
	}

	updateStatus('<?php echo $engine->getState(); ?>');
</script>

</body>
</html>