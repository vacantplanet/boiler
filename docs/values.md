---
title: Displaying Values
---
Displaying Values
=================


If you pass a value of type `VacantPlanet\Boiler\Value` to `e`/`escape` it will automatically
be unwrapped before it is passed to {{php('htmlspecialchars')}}.

## Changing the arguments passed to {{php('htmlspecialchars')}}

Wrapped values pass the flags `ENT_QUOTES | ENT_SUBSTITUTE` and the encoding `UTF-8` 
when calling PHP's {{php('htmlspecialchars')}} function 
internally. If you need to override these defaults use the template helper method `e` or its long form `escape`:

	$this->e($value, ENT_NOQUOTES | ENT_HTML401, 'EUC-JP');
	$this->e(
		value: $value, 
		flags: ENT_NOQUOTES | ENT_HTML401, 
		encoding: 'EUC-JP'
	);

	// or 
	$this->escape($value, ENT_NOQUOTES | ENT_HTML401, 'EUC-JP');
