<?php

	function state($fn) {
		return function($input, $stack = []) use ($fn) {
			try {
				$r = $fn($input, $stack);
				if ($r === null) {
					throw new Exception();
				}
			} catch (Exception $e) {
				throw new ValueError("Invalid JSON $input in " . $fn->getName());
			}
			return $r;
		};
	}

	function state_start($input) {
		return state_root_object($input, ["$"]);
	}

	$state_root_object = state(function($input, $stack) {
		$input = trim($input);

		if ($input[0] === "[") {
			return $input[0] . state_value(substr($input, 1), array_merge($stack, ["["]));
		} elseif ($input[0] === "{") {
			return $input[0] . state_object(substr($input, 1), array_merge($stack, ["{"]));
		} else {
			if (strpos($input, "json") === 0) {
				return state_root_object(trim(substr($input, 4)), $stack);
			}
		}
		return null;
	});

	$state_finish = state(function($input, $stack) {
		$input = trim($input);

		if (empty($input)) {
			return "";
		}

		return null;
	});

	$state_object = state(function($input, $stack) {
		$input = trim($input);

		if ($input[0] === "}" && end($stack) === "{") {
			return $input[0] . state_post_object(substr($input, 1), array_slice($stack, 0, -1));
		}

		if ($input[0] === '"') {
			return $input[0] . state_property_string(substr($input, 1), $stack);
		}
		return null;
	});

	$state_post_object = state(function($input, $stack) {
		if (in_array(end($stack), ["{", "["])) {
			return state_post_value($input, $stack);
		}

		if (end($stack) === "$") {
			return state_finish($input, $stack);
		}
		return null;
	});

	$state_post_property = state(function($input, $stack) {
		$input = trim($input);

		if ($input[0] === ":") {
			return $input[0] . state_value(substr($input, 1), $stack);
		}
		return null;
	});

	$state_value = state(function($input, $stack) {
		$input = trim($input);

		if ($input[0] === "f") {
			if (substr($input, 0, 5) !== "false") {
				throw new Exception();
			}
			return substr($input, 0, 5) . state_post_value(substr($input, 5), $stack);
		}
		if ($input[0] === "t") {
			if (substr($input, 0, 4) !== "true") {
				throw new Exception();
			}
			return substr($input, 0, 4) . state_post_value(substr($input, 4), $stack);
		}
		if ($input[0] === "n") {
			if (substr($input, 0, 4) !== "null") {
				throw new Exception();
			}
			return substr($input, 0, 4) . state_post_value(substr($input, 4), $stack);
		}
		if (ctype_digit($input[0]) || $input[0] === "-") {
			return $input[0] . state_int(substr($input, 1), $stack);
		}
		if ($input[0] === '"') {
			return $input[0] . state_value_string(substr($input, 1), $stack);
		}
		if ($input[0] === "{") {
			return $input[0] . state_object(substr($input, 1), array_merge($stack, ["{"]));
		}
		if ($input[0] === "[") {
			return $input[0] . state_value(substr($input, 1), array_merge($stack, ["["]));
		}
		if ($input[0] === "]") {
			if (end($stack) !== "[") {
				throw new Exception();
			}
			return $input[0] . state_post_value(substr($input, 1), array_slice($stack, 0, -1));
		}

		return null;
	});

	$state_post_value = state(function($input, $stack) {
		$input = trim($input);

		if (end($stack) === "$") {
			return state_finish($input, $stack);
		}

		if ($input[0] === ",") {
			if (in_array($input[1], ["}", "]"])) {
				return state_post_value(substr($input, 1), $stack);
			}
			if (end($stack) === "[") {
				return $input[0] . state_value(substr($input, 1), $stack);
			}
			if (end($stack) === "{") {
				return $input[0] . state_object(substr($input, 1), $stack);
			}
			return null;
		} elseif ($input[0] === "]") {
			if (end($stack) === "[") {
				return $input[0] . state_post_value(substr($input, 1), array_slice($stack, 0, -1));
			}
			if (end($stack) !== "{") {
				throw new Exception();
			}
			return state_post_value("}" . $input, $stack);
		} elseif ($input[0] === "}") {
			if (end($stack) === "{") {
				return $input[0] . state_post_value(substr($input, 1), array_slice($stack, 0, -1));
			}
			if (end($stack) !== "[") {
				throw new Exception();
			}
			return state_post_value("]" . $input, $stack);
		}
		return null;
	});

	$state_value_string = state(function($input, $stack) {
		for ($i = 0; $i < strlen($input); $i++) {
			if ($input[$i] === '"') {
				try {
					return substr($input, 0, $i + 1) . state_post_value(substr($input, $i + 1), $stack);
				} catch (Exception $e) {
					return substr($input, 0, $i) . state_value_string("\\" . substr($input, $i), $stack);
				}
			}

			if ($input[$i] === "\\") {
				try {
					return substr($input, 0, $i + 1) . state_escape_char(substr($input, $i + 1), array_merge($stack, ["v"]));
				} catch (Exception $e) {
					return substr($input, 0, $i) . state_value_string(substr($input, $i + 1), $stack);
				}
			}
		}

		return null;
	});

	$state_property_string = state(function($input, $stack) {
		for ($i = 0; $i < strlen($input); $i++) {
			if ($input[$i] === '"') {
				return substr($input, 0, $i + 1) . state_post_property(substr($input, $i + 1), $stack);
			}
			if ($input[$i] === "\\") {
				try {
					return substr($input, 0, $i + 1) . state_escape_char(substr($input, $i + 1), array_merge($stack, ["p"]));
				} catch (Exception $e) {
					return substr($input, 0, $i) . state_property_string(substr($input, $i + 1), $stack);
				}
			}
		}

		return null;
	});

	$state_escape_char = state(function($input, $stack) {
		if ($input[0] === "u") {
			hexdec(substr($input, 1, 4));

			if (end($stack) === "v") {
				return substr($input, 0, 5) . state_value_string(substr($input, 5), array_slice($stack, 0, -1));
			}
			if (end($stack) === "p") {
				return substr($input, 0, 5) . state_property_string(substr($input, 5), array_slice($stack, 0, -1));
			}

			return null;
		}

		if (in_array($input[0], ["\\", "/", '"', "b", "f", "n", "r", "t"])) {
			if (end($stack) === "v") {
				return $input[0] . state_value_string(substr($input, 1), array_slice($stack, 0, -1));
			}
			if (end($stack) === "p") {
				return $input[0] . state_property_string(substr($input, 1), array_slice($stack, 0, -1));
			}
			return null;
		}
		return null;
	});

	$state_int = state(function($input, $stack) {
		if (ctype_digit($input[0])) {
			return $input[0] . state_int(substr($input, 1), $stack);
		}
		if ($input[0] === ".") {
			return $input[0] . state_double(substr($input, 1), $stack);
		}
		if ($input[0] === ",") {
			if (end($stack) === "[") {
				return $input[0] . state_value(substr($input, 1), $stack);
			}
			if (end($stack) === "{") {
				return $input[0] . state_object(substr($input, 1), $stack);
			}
			return null;
		}
		if ($input[0] === "}") {
			if (end($stack) !== "{") {
				throw new Exception();
			}
			return $input[0] . state_post_int_parent(substr($input, 1), $stack);
		}
		if ($input[0] === "]") {
			if (end($stack) !== "[") {
				throw new Exception();
			}
			return $input[0] . state_post_int_parent(substr($input, 1), $stack);
		}
		if (ctype_space($input[0])) {
			return $input[0] . state_post_value(substr($input, 1), $stack);
		}
		if (in_array($input[0], ["e", "E"])) {
			return $input[0] . state_exponent_sign(substr($input, 1), $stack);
		}

		return null;
	});

	$state_post_int_parent = state(function($input, $stack) {
		if (end($stack) === "[") {
			return state_post_value($input, array_slice($stack, 0, -1));
		}
		if (end($stack) === "{") {
			return state_post_object($input, array_slice($stack, 0, -1));
		}
		return null;
	});

	$state_double = state(function($input, $stack) {
		if (ctype_digit($input[0])) {
			return $input[0] . state_double(substr($input, 1), $stack);
		}
		if ($input[0] === ",") {
			if (end($stack) === "[") {
				return $input[0] . state_value(substr($input, 1), $stack);
			}
			if (end($stack) === "{") {
				return $input[0] . state_object(substr($input, 1), $stack);
			}
			return null;
		}
		if ($input[0] === "}") {
			if (end($stack) !== "{") {
				throw new Exception();
			}
			return $input[0] . state_post_int_parent(substr($input, 1), $stack);
		}
		if ($input[0] === "]") {
			if (end($stack) !== "[") {
				throw new Exception();
			}
			return $input[0] . state_post_int_parent(substr($input, 1), $stack);
		}
		if (ctype_space($input[0])) {
			return $input[0] . state_post_value(substr($input, 1), $stack);
		}
		if (in_array($input[0], ["e", "E"])) {
			return $input[0] . state_exponent_sign(substr($input, 1), $stack);
		}
		return null;
	});

	$state_exponent_sign = state(function($input, $stack) {
		if (ctype_digit($input[0])) {
			return $input[0] . state_exponent_digits(substr($input, 1), $stack);
		}

		if (in_array($input[0], ["+", "-"])) {
			return $input[0] . state_exponent_digits(substr($input, 1), $stack);
		}
		return null;
	});

	$state_exponent_digits = state(function($input, $stack) {
		if (ctype_digit($input[0])) {
			return $input[0] . state_exponent_digits(substr($input, 1), $stack);
		}
		if ($input[0] === ",") {
			if (end($stack) === "[") {
				return $input[0] . state_value(substr($input, 1), $stack);
			}
			if (end($stack) === "{") {
				return $input[0] . state_object(substr($input, 1), $stack);
			}
			return null;
		}
		if ($input[0] === "}") {
			if (end($stack) !== "{") {
				throw new Exception();
			}
			return $input[0] . state_post_int_parent(substr($input, 1), $stack);
		}
		if ($input[0] === "]") {
			if (end($stack) !== "[") {
				throw new Exception();
			}
			return $input[0] . state_post_int_parent(substr($input, 1), $stack);
		}
		if (ctype_space($input[0])) {
			return $input[0] . state_post_value(substr($input, 1), $stack);
		}
		return null;
	});

	function repair_json($json_str) {
		$json_str = trim($json_str);
		return state_start($json_str);
	}

	function base_loads($json_str) {
		try {
			return json_decode($json_str, true, 512, JSON_THROW_ON_ERROR);
		} catch (Exception $e) {
			return json_decode($json_str, true);
		}
	}

	function loads($json_str, $auto_repair = true) {
		try {
			return base_loads($json_str);
		} catch (Exception $e) {
			if (!$auto_repair) {
				throw $e;
			}
		}

		try {
			$repaired_json = repair_json($json_str);
		} catch (Exception $e) {
			throw new Exception("Failed to repair JSON: " . $e->getMessage());
		}

		return base_loads($repaired_json);
	}

?>
