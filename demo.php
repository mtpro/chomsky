<?php
	require_once("cfg.php");

	# This code is in the public domain.
	# http://qntm.org/chomsky

	function demo($cfg) {
		print("Old start symbol is: ".$cfg->getStartSymbol()."\n");
		print("Old Backus-Naur Form rules are:\n");
		$cfg->printRules();

		$cfg->toCnf();
		print("New start symbol is: ".$cfg->getStartSymbol()."\n");
		print("New Chomsky Normal Form rules are:\n");
		$cfg->printRules();
	}

	// demonstrate eliminateTerminals()
	// <S> -> <A> c
	// <A> -> a | b
	demo(
		new ContextFreeGrammar(
			"<S>",
			array("0", "1"),
			array("<A>", "<B>"),
			array(
				new Rule("<S>", array("<A>", "<B>", "<B>", "<A>")),
				new Rule("<S>", array("<B>")),
				new Rule("<A>", array()),
				new Rule("<A>", array("0", "<A>", "0")),
				new Rule("<A>", array("1")),
				new Rule("<B>", array("<B>", "<S>")),
				new Rule("<B>", array("<A>", "<A>", "<A>")), 
				new Rule("<B>", array("0", "0"))
			)
		)
	);

	// demonstrate eliminateMultiples()
	// <S> -> <A> <B> <C>
	// <A> -> a
	// <B> -> b
	// <C> -> c
	demo(
		new ContextFreeGrammar(
			"<S>",
			array("a", "b", "c"),
			array("<A>", "<B>", "<C>"),
			array(
				new Rule("<S>", array("<A>", "<B>", "<C>")),
				new Rule("<A>", array("a")),
				new Rule("<B>", array("b")),
				new Rule("<C>", array("c"))
			)
		)
	);
	
	// <S> -> <A>
	// <A> -> a
	demo(
		new ContextFreeGrammar(
			"<S>",
			array("a"),
			array("<A>"),
			array(
				new Rule("<S>", array("<A>")),
				new Rule("<A>", array("a"))
			)
		)
	);

	// <S> -> <A> <A>
	// <A> -> epsilon
	// <A> -> a
	demo(
		new ContextFreeGrammar(
			"<S>",
			array("a"),
			array("<A>"),
			array(
				new Rule("<S>", array("<A>", "<A>")),
				new Rule("<A>", array()),
				new Rule("<A>", array("a"))
			)
		)
	);

	// <S> -> <A> b <A> | <B>
	// <B> -> b | c
	// <A> -> epsilon
	demo(
		new ContextFreeGrammar(
			"<S>",
			array("a", "b", "c"),
			array("<A>", "<B>"),
			array(
				new Rule("<S>", array("<A>", "b", "<A>")),
				new Rule("<S>", array("<B>")),
				new Rule("<B>", array("b")),
				new Rule("<B>", array("c")),
				new Rule("<A>", array())
			)
		)
	);

	// A rough approximation of the BNF specification
	demo(
		new ContextFreeGrammar(
			"<S>",
			array("\n", ":", "=", "|", "<", ">", "a", "b", "c", "d", "\"", " "),
			array("<rule>", "<expression>", "<list>", "<term>", "<ruleref>", "<rulename>", "<alpha>", "<literal>", "<text>", "<whitespace>"),
			array(
				new Rule("<S>", array()),
				new Rule("<S>", array("<whitespace>", "<rule>", "<S>")),
				new Rule("<rule>", array("\n")),
				new Rule("<rule>", array("<ruleref>", "<whitespace>", ":", ":", "=", "<whitespace>", "<list>", "<expression>", "\n")),
				new Rule("<expression>", array()),
				new Rule("<expression>", array("|", "<whitespace>", "<list>", "<expression>")),
				new Rule("<list>", array()),
				new Rule("<list>", array("<term>", "<whitespace>", "<list>")),
				new Rule("<term>", array("<ruleref>")),
				new Rule("<term>", array("<literal>")),
				new Rule("<ruleref>", array("<", "<rulename>", ">")),
				new Rule("<rulename>", array()),
				new Rule("<rulename>", array("<alpha>", "<rulename>")),
				new Rule("<alpha>", array("a")),
				new Rule("<alpha>", array("b")),
				new Rule("<alpha>", array("c")),
				new Rule("<alpha>", array("d")),
				new Rule("<literal>", array("\"", "<text>", "\"")),
				new Rule("<text>", array()),
				new Rule("<text>", array("<alpha>", "<text>")),
				new Rule("<whitespace>", array()),
				new Rule("<whitespace>", array(" ", "<whitespace>"))
			)
		)
	);
?>
