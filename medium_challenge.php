<html> 
<head> 
	<title> Medium Word Count Challenge </title> 
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	 <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>
    <script src="validation.js"> </script> 
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</head> 

<body> 
	<?php if($_SERVER["REQUEST_METHOD"] != "POST"){ ?>
	<div class="col-xs-1"> </div>
	<div class="col-xs-11"> 
		<div class="jumbotron">
			<form action="medium_challenge.php" method="post" id="text_form" class="form-horizontal" role="form"> 
			<h2> Paste in your text here! </h2> 
			<textarea id="text" class="form-control" rows="3" name="text"> </textarea> <br> 
			<button type="submit" class="btn btn-primary" onclick="return validateForm();"> Submit!</button> 
		</div>
	</form>
</div>
	<?php }else{ ?>
	<?php 
		function estimate_syllables($word_arr) {
			$total_count=0; 
			foreach($word_arr as $w) {
				$syllable_count = count_english_vowels($w);
				$total_count += $syllable_count; 
			}
            return $total_count;
    	}
    	//syllable count formula generated from an old CS106B assignment (I never did this, but found it through google) http://www.stanford.edu/class/archive/cs/cs106b/cs106b.1136/handouts/050%20Assignment%201.pdf
		function count_english_vowels($word) {
    		static $english_vowels = array('A', 'E', 'I', 'O', 'U', 'Y');
    		$vowel_count = 0;
    		$letters = preg_replace('/[^a-z0-9]+/i', "", $word);
    		$len = strlen($letters); 
    		$letters = str_split(strtoupper($letters)); 
    		$currPosition = -2; 
    		$prevPosition = -1;
    		for($i = 0; $i < $len; $i++) {
        		if (in_array($letters[$i], $english_vowels)) {
        			if($i != $currPosition + 1) {
        				$vowel_count++;
        				$prevPosition = $currPosition; 
        		 		$currPosition = $i;
        				if ($letters[$i] == 'E' && $i == ($len -1))
        					$vowel_count--; 
        			} 
        		}
    		}
    		return $vowel_count;
		}

		//outputs scraped from Wikipedia
		function readability_output($score) {
			if ($score < 30.0) {
				return "This piece of text is very confusing"; 
			} else if ($score >= 30.0 && $score < 50.0) {
				return "This piece of text is difficult."; 
			} else if ($score >= 50.0 && $score < 60.0) {
				return "This piece of text is fairly difficult"; 
			} else if ($score >= 60 && $score < 70) {
				return "This piece of text is standard"; 
			} else if ($score >= 70 && $score < 80) {
				return "This piece of text is fairly easy"; 
			} else if ($score >= 80 && $score < 90) {
				return "This piece of text is easy"; 
			} else {
				return "This piece of text is very easy!"; 
			}
		}

		//most readable texts, least readable texts, random text
		function database_insert($text, $flesch_ease, $flesch_grade) {
			$con = mysqli_connect("localhost","root");
			if (mysqli_connect_errno())
			{
  				echo "Failed to connect to MySQL: " . mysqli_connect_error();
			}
			mysqli_select_db($con, "mediumdb"); 
			$stmt = "INSERT INTO Entries(Entry, Flesch_Kincaid_Ease, Flesch_Kincaid_Grade) VALUES('$text', '$flesch_ease', '$flesch_grade')"; 
			if (mysqli_query($con, $stmt)) {
				$result = mysqli_query($con, "select * from Entries ORDER BY Flesch_Kincaid_Ease"); 
				echo "<tr class='success'> <th> Entry </th> <th> Flesch-Kincaid Ease Level </th> </tr>"; 
				while ($row = mysqli_fetch_array($result)) { 
					echo "<tr> <td>" . $row['Entry'] . "</td>";   
					echo "<td>" .  $row['Flesch_Kincaid_Ease'] . "</td> </tr>"; 
	 			} 
	 			mysqli_free_result($result); 
	 			mysqli_close($con); 
			} else {
				echo "error adding that entry " . mysqli_error($con); 
			}
		}

		function print_flesch_grade_levels() {
			$con = mysqli_connect("localhost","root");
			if (mysqli_connect_errno())
			{
  				echo "Failed to connect to MySQL: " . mysqli_connect_error();
			}
			mysqli_select_db($con, "mediumdb"); 
			$result = mysqli_query($con, "select * from Entries ORDER BY Flesch_Kincaid_Grade"); 
	 		echo "<tr class='success'> <th> Entry </th> <th> Flesch-Kincaid Grade Level </th> </tr>"; 
	 		while($row = mysqli_fetch_array($result)) {
	 			echo "<tr> <td> " . $row['Entry'] . "</td>";  
	 			echo "<td>" . $row['Flesch_Kincaid_Grade']. "</td> </tr>"; 
	 		}
	 		mysqli_free_result($result); 
	 		mysqli_close($con); 
		}

		//calculates all the relevant statistics relating to a piece of text. 
		$text = $_POST['text']; 
		$words = str_word_count($text); //num words
		$sentences =  preg_split('/[.?!]/', $text); //num sentences, return an array
		$paragraphs = explode("\n", $text);
		$delimited_words = explode(" ", $text); 
		$bigrams=array(); 
		$len = count($delimited_words); 
		for ($i = 0; $i <= $len -2; $i++) {
			$word_one = preg_replace('/[^a-z0-9]+/i', '', $delimited_words[$i]); //strip punctuation
			$word_two = preg_replace('/[^a-z0-9]+/i', '', $delimited_words[$i + 1]); //strip punctuation
			if ($word_one != "" && $word_two != "") { //check for empty space before and after entering
				$val = $word_one . '-' . $word_two; 
				if (!in_array($val, $bigrams)) {
					$bigrams[] = $val; 
				}
			}
		}
		$numSentences = count($sentences) -1; 
		$numSyllables = estimate_syllables($delimited_words); 
		$flesch_kinkaid_ease = 206.835 - (1.015 * ($words/$numSentences)) - (84.6* ($numSyllables/$words)); 
		$flesch_kinkaid_grade = (0.39* ($words/$numSentences)) + (11.8 *($numSyllables/$words)) - 15.59; 
		$flesch_kinkaid_grade = round($flesch_kinkaid_grade, 1); 
		$flesch_kinkaid_ease = round($flesch_kinkaid_ease, 1);
		 ?>
	<div class="jumbotron"> 
		<h1> Your Word Statistics </h1>
		<?php 
		echo "<li> Number of words: " . $words . "</li>"; 
		echo "<li>Number of sentences: " . $numSentences. "</li>"; 
		echo "<li>Number of paragraphs: " . (count($paragraphs)). "</li>"; 
		echo "<li>Number of bigrams: " . (count($bigrams)). "</li>"; 
		echo "<li>Syllable count: " . $numSyllables. "</li>"; 
		echo "<li>Flesch Kinkaid Ease: ". $flesch_kinkaid_ease . "</li>";
		echo "<li>" . readability_output($flesch_kinkaid_ease). "</li>"; 
		echo "<li>Flesch Kinkaid Grade Level: ". $flesch_kinkaid_grade. "</li>"; 
		?>
    </div>
    	<div class="col-xs-1"> </div> 
    	<div class="col-xs-11"> 
    	<h2> Here are the entries ordered by the Flesch Kincaid Ease! </h2>
    	<table class="table table-condensed"> 
    		<?php database_insert($text, $flesch_kinkaid_ease, $flesch_kinkaid_grade); ?>
    	</table> </div>
    	<div class="col-xs-1"> </div> 
    	<div class="col-xs-11"> 
    	<h2> Here are the entries ordered by the Flesch Kincaid Grade! </h2> 
    	<table class="table table-condensed"> 
    		<?php print_flesch_grade_levels(); }?>
    	</table> </div>
</body> 
</html> 