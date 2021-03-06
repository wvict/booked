<?php
	include "Database.php";


	class BookEvent extends Database{

		public function add_book_event($user_id, $book_title, $author_name, $category_id, $month_id, $classification, $year_id, $task_date, $hash_id, $book_cover_url){

			$sql = "INSERT INTO add_book (user_id, book_title, author_name, catg_id, month_id, year_id, classification, task_date, hash_id, book_cover_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $book_title, $author_name, $category_id, $month_id,$classification, $year_id, $task_date, $hash_id, $book_cover_url]);
			return true;
		}

		public function get_add_book_id($user_id, $book_title){

			$sql = "SELECT * FROM add_book WHERE user_id = ? AND book_title = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $book_title]);
			$result = $stmt->fetch();

			return $result['id'];
		}

		public function add_category($category){

			$sql = "SELECT * FROM categories WHERE catg_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$category]);
			$result = $stmt->fetch();

			if(empty($result)){
				$sql2 = "INSERT INTO categories (catg_name) VALUES (?)";
				$stmt2 = $this->connect()->prepare($sql2);
				$stmt2->execute([$category]);

				return true;
			}
		}

		public function add_year($year){

			$sql = "SELECT * FROM year_finished WHERE year_number = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$year]);
			$result = $stmt->fetch();

			if(empty($result)){
				$sql2 = "INSERT INTO year_finished (year_number) VALUES (?)";
				$stmt2 = $this->connect()->prepare($sql2);
				$stmt2->execute([$year]);

				return true;
			}
		}

		public function select_month_id($month){

			$sql = "SELECT id FROM month_finished WHERE month_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$month]);

			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				return false;
			}
		}

		public function select_year_id($year){
			$sql = "SELECT id FROM year_finished WHERE year_number = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$year]);

			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				return false;
			}
		}

		public function select_category_id($category){

			$sql = "SELECT id FROM categories WHERE catg_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$category]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				return false;
			}
		}

		public function download_book_cover_url($book_title, $author_name){
			//Download the book cover into the file "bookcovers" where the user add a new book read.
			try{
				return shell_exec('python3 ../python/get_book_url_img.py "'.$book_title.'" "'.$author_name.'" ');
			}
			catch(PDOException $e){
				return "ERROR: ".$e->getMessage;
			}

		}

		public function get_book_cover_url($hash_id){//gets bookcover url
			$sql = "SELECT * FROM add_book WHERE hash_id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$hash_id]);
			$result = $stmt->fetch();

			return $result['book_cover_url'];
		}

		//FUNCTIONS USED IN THE index.php page!

		public function display_years_homepage($user_id){//gets all the years the user read books
			$sql = "SELECT DISTINCT year_number FROM add_book
				JOIN users ON user_id = users.id
				JOIN categories ON catg_id = categories.id
				JOIN month_finished ON month_id = month_finished.id
				JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? ORDER BY year_number;"; //This will display only the years where the user added books into his/her list!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id]);
			$result = $stmt->fetchAll();

			return $result;
		}

		public function display_years_input($user_id){

			$sql = "SELECT DISTINCT year_number FROM add_book
				JOIN users ON user_id = users.id
				JOIN categories ON catg_id = categories.id
				JOIN month_finished ON month_id = month_finished.id
				JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? ORDER BY year_number;"; //This will display only the years where the user added books into his/her list!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id]);
			$result = $stmt->fetchAll();
			return $result;
		}

		public function get_smallest_year(){
			$sql = "SELECT MIN(year_number) AS smallest_year FROM year_finished";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch();
			return $result['smallest_year'];
		}

		public function display_books_year($user_id, $year){//gets data about the books read in a particular year
			$sql = "SELECT add_book.id as add_book_id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, hash_id FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND year_number = ? ORDER BY month_id, add_book.id;"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $year]);
			$result = $stmt->fetchAll();
			return $result;
		}

		public function refresh_books_search($user_id, $search_term){
			try{
				$sql = "SELECT add_book.id as add_book_id, book_title, author_name, catg_name, month_name, year_number, task_date, hash_id FROM add_book
			JOIN users ON user_id = users.id
			JOIN categories ON catg_id = categories.id
			JOIN month_finished ON month_id = month_finished.id
			JOIN year_finished ON year_id = year_finished.id
			WHERE user_id = ? HAVING book_title LIKE ? OR author_name LIKE ?
			OR catg_name LIKE ? ORDER BY year_id DESC";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $search_term, $search_term, $search_term]);
			$result = $stmt->fetchAll();
			return $result;
			
			}
			catch(PDOException $e){
				return 'ERROR: '.$e->getMessage();
			}
			
		}

		public function refresh_books_year($user_id, $year){ //I created this function because of problems with the location of the bookcovers!
			$sql = "SELECT add_book.id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, hash_id FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND year_number = ? ORDER BY month_id, add_book.id;"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $year]);
			$result = $stmt->fetchAll();

?>
			<div class="books">
			<p style="color: white; font-size:30px; margin:0 0 5px 15px">Books read in <?php echo $year; ?>:</p>
<?php
			foreach($result as $data){ ?>

			<div class="box" value="<?php echo $data['month_name'] ?>">


			<?php
			$location = $this->get_book_cover_url($data['hash_id']);


			echo '<img class="cover" src="'.$location.'">';
			?>
			<div class="book_info">
				<p class="title"><?php echo $data['book_title']; ?></p> <br>
				<p class="author">Author: <span><?php echo $data['author_name']; ?></span></p>
				<p class="category">Category: <span><?php echo $data['catg_name']; ?></span></p>
				<p class="month">Month finished: <span><?php echo $data['month_name']; ?></span></p>
				<p class="date">Date added: <span><?php echo $data['task_date']; ?></span></p>

				<div class="rating">
					<p><span class="grade"><?php echo $data['classification']?></span></p>
				</div>
			</div>

			<?php


			?>
			<div><!-- The data goes from here to the javascript.js file and then I use AJAX to pass the data to an includes file called delete_book.php and from there the book is deleted! -->
				<input hidden class="delete_book_input" value="<?php echo $data['hash_id'] ?>">
				<button class="edit_book_button" onclick="editReadingEvent('<?php echo $data['hash_id']; ?>')"><img alt="edit books' information" src="images/edit.png"></button>
				<button onclick="deleteBook('<?php echo $data['hash_id']; ?>', <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash-can.svg"></button>
			</div>


			</div>

			<?php
			}
		echo '</div>';
		}

		public function refresh_books_month($user_id, $year, $month){//I created this function because of problems with the location of the bookcovers!

			$sql = "SELECT add_book.id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, hash_id FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND year_number = ? and month_name = ? ORDER BY month_id, add_book.id;"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $year, $month_name]);
			$result = $stmt->fetchAll();


			echo '<div class="books">';
			foreach($result as $data){ ?>

			<div class="box" value="<?php echo $data['month_name'] ?>">


			<?php
			$book = new BookEvent();
			$location = $book->get_book_cover_url($data['hash_id']);


			echo '<img class="cover" src="'.$location.'">';
			?>
			<div class="book_info">
				<p class="title"><?php echo $data['book_title']; ?></p> <br>
				<p class="author">Author: <span><?php echo $data['author_name']; ?></span></p>
				<p class="category">Category: <span><?php echo $data['catg_name']; ?></span></p>
				<p class="month">Month finished: <span><?php echo $data['month_name']; ?></span></p>
				<p class="date">Date added: <span><?php echo $data['task_date']; ?></span></p>

				<div class="rating">
					<p><span class="grade"><?php echo $data['classification']?></span></p>
				</div>
			</div>

			<?php


			?>
			<div><!-- The data goes from here to the javascript.js file and then I use AJAX to pass the data to an includes file called delete_book.php and from there the book is deleted! -->
				<input hidden class="delete_book_input" value="<?php echo $data['hash_id'] ?>">
				<button class="edit_book_button" onclick="editReadingEvent('<?php echo $data['hash_id']; ?>')"><img alt="edit books' information" src="images/edit.png"></button>
				<button onclick="deleteBook('<?php echo $data['hash_id']; ?>', <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash-can.svg"></button>
			</div>


			</div>

			<?php
			}
		echo '</div>';
		}


		public function refresh_book($hash_id){

			$sql = "SELECT add_book.id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, hash_id FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE hash_id = ?"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$hash_id]);
			$data = $stmt->fetch();

			?>
			<div class="books">

			<div class="box" value="<?php echo $data['month_name'] ?>">

			<?php
			$book = new BookEvent();
			$location = $book->get_book_cover_url($data['hash_id']);


			echo '<img class="cover" src="'.$location.'">';

			?>
			<div class="book_info">
				<p class="title"><?php echo $data['book_title']; ?></p> <br>
				<p class="author">Author: <span><?php echo $data['author_name']; ?></span></p>
				<p class="category">Category: <span><?php echo $data['catg_name']; ?></span></p>
				<p class="month">Month finished: <span><?php echo $data['month_name']; ?></span></p>
				<p class="date">Date added: <span><?php echo $data['task_date']; ?></span></p>

				<div class="rating">
					<p><span class="grade"><?php echo $data['classification']?></span>%</p>
				</div>
			</div>

			<?php


			?>
			<div><!-- The data goes from here to the javascript.js file and then I use AJAX to pass the data to an includes file called delete_book.php and from there the book is deleted! -->
				<input hidden class="delete_book_input" value="<?php echo $data['hash_id'] ?>">
				<button class="edit_book_button" onclick="editReadingEvent('<?php echo $data['hash_id']; ?>')"><img alt="edit books' information" src="images/edit.png"></button>
				<button onclick="deleteBook('<?php echo $data['hash_id']; ?>', <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash-can.svg"></button>
			</div>


			</div>

			<?php

		echo '</div>';
		}



		public function display_books_month($user_id,$month, $year){

			$sql = "SELECT add_book.id as add_book_id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, hash_id FROM add_book JOIN users ON user_id = users.id  JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND year_number = ? AND month_name = ? ORDER BY month_id, add_book.id"; //When I use ORDER BY id DESC that means it will display always the last row added!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $year, $month]);
			$result = $stmt->fetchAll();
			if(empty($result)){
				echo '<p style="color: white; font-size:30px; margin:0 0 5px 15px">Books not found. Go to <a style="color: white" href="dashboard.php">dashboard</a></p>';
			}
			else{?>
			<div class="books">
				<p class="text-month">Books read in <?php echo $month; ?> of <?php echo $year; ?>:</p>
<?php

				foreach($result as $data){ ?>

				<div class="box">
					<?php
					$book = new BookEvent();
					$location = $book->get_book_cover_url($data['hash_id']);


					echo '<img class="cover" src="'.$location.'">';
					?>
					<div class="book_info">
						<p class="title"><?php echo $data['book_title']; ?></p> <br>
						<p class="author">Author: <span><?php echo $data['author_name']; ?></span></p>
						<p class="category">Category: <span><?php echo $data['catg_name']; ?></span></p>
						<p class="month">Month finished: <span><?php echo $data['month_name']; ?></span></p>
						<p class="date">Date added: <span><?php echo $data['task_date']; ?></span></p>
						<div class="rating">
							<p><span class="grade"><?php echo $data['classification']?></span></p>
						</div>
					</div>
					<div><!-- The data goes from here to the javascript.js file and then I use AJAX to pass the data to an includes file called delete_book.php and from there the book is deleted! -->
						<input hidden class="delete_book_input" value="<?php echo $data['hash_id'] ?>">
						<button class="edit_book_button" onclick="editReadingEvent('<?php echo $data['hash_id']; ?>')"><img alt="edit books' information" src="images/edit.png"></button>
						<button onclick="deleteBook('<?php echo $data['hash_id']; ?>', <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash-can.svg"></button>
					</div>

				</div>
<?php 			} ?>

			</div>

<?php
			}

		}

		public function books_read_month($user_id, $year, $month){

			$sql = "SELECT COUNT(book_title) AS books_read, month_name, year_number FROM add_book
			JOIN users ON user_id = users.id
			JOIN categories ON catg_id = categories.id
			JOIN month_finished ON month_id = month_finished.id
			JOIN year_finished ON year_id = year_finished.id
			WHERE user_id = ? AND year_number = ? AND month_name = ? GROUP BY month_name";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $year, $month]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['books_read'];
			}
			else{
				return '0';
			}

		}

		public function books_read_year($user_id, $year){


			$sql = "SELECT COUNT(book_title) AS books_read FROM add_book
			JOIN users ON user_id = users.id
			JOIN categories ON catg_id = categories.id
			JOIN month_finished ON month_id = month_finished.id
			JOIN year_finished ON year_id = year_finished.id
			WHERE user_id = ? AND year_number = ? GROUP BY year_number";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $year]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['books_read'];
			}
			else{
				return '0';
			}


		}

		public function search($user_id, $search_term){

			$sql = "SELECT add_book.id as add_book_id, book_title, author_name, catg_name, month_name, year_number, task_date, hash_id FROM add_book
			JOIN users ON user_id = users.id
			JOIN categories ON catg_id = categories.id
			JOIN month_finished ON month_id = month_finished.id
			JOIN year_finished ON year_id = year_finished.id
			WHERE user_id = ? HAVING book_title LIKE ? OR author_name LIKE ?
			OR catg_name LIKE ? ORDER BY year_id DESC";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $search_term, $search_term, $search_term]);
			$result = $stmt->fetchAll();
			return $result;
		}

		public function display_edit_book($user_id, $hash_id){

			$sql = "SELECT add_book.id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, hash_id FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND hash_id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $hash_id]);
			$result = $stmt->fetchAll();

			if(empty($result)){
				echo "<p>Something went wrong!</p>";
			}

			foreach($result as $data){
			?>
			<div class="box_edit">

				<?php
				$book = new BookEvent();
				$location = $book->get_book_cover_url($data['hash_id']);
				?>

				<img class="book-cover-edit" src=" <?php echo $location; ?>">

				<form method="POST" action="includes/edit_book.php">
					<p class="title_text">Title: </p>
					<?php echo ' <input class="edit_title_name" spellcheck="false" type="text" name="book_title" value="'.$data["book_title"].'"><br> '?>
					<p class="author_text">Author: </p>
					<?php echo ' <input spellcheck="false"  class="edit_authors_name" type="text" name="author_name" value="'.$data["author_name"].'"><br>  '?>
					<p class="category_text">Category:</p>
					<?php echo ' <p class="edit_category_name">'.$data["catg_name"].'</p><br>  '?>
					<p class="month_text">Month finished:</p><br>
					<?php
						
						echo $this->display_months_edit($hash_id);?><br>
					<p class="year_text">Year finished:</p>
					<?php echo ' <p class="edit_year_number">'.$data["year_number"].'</p> '?>
					<p class="classification">Grade:</p>
					<input name="classification" class="classification_input" value="<?php echo $data['classification']; ?>">
					<input style="display:none" name="hash_id" value=" <?php echo $hash_id;?>"> <?php //In here a created a input that stores the hash_id. It is then passed to the edit_book.php file to update the values. ?> 

					<div>
						<input hidden class="hash_id_input" value="<?php echo $hash_id; ?>" name="hash_id" type="text">
						<input hidden class="type-reload" name="type-reload" value="<?php  ?>">

					</div>
				</form>
			</div>
			<?php

			}
		}

		public function update_book_title ($hash_id, $book_title) {//Updates the book's title!
			$sql = "UPDATE add_book
			SET book_title = ? WHERE hash_id= ?";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$book_title, $hash_id]);
			return true;
		}

		public function update_author_name ($hash_id, $author_name) {//Updates the author's name
			$sql = "UPDATE add_book
			SET author_name = ? WHERE hash_id = ?;";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$author_name, $hash_id]);

		}

		public function update_category ($hash_id, $category) {//Updates the category's name

			$this->add_category($category); //adds the category if it's not in the DB yet. If it is, nothing happens because:
			$catg_id = $this->select_category_id($category); //this will select the id of the category.

			$sql = "UPDATE add_book
			JOIN users ON user_id = users.id
			JOIN categories ON catg_id = categories.id
			SET catg_id = ? WHERE hash_id = ?;";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$catg_id, $hash_id]);

			//On this one, I need to implement a system so that the user can only add, edit and delete their categories.

		}

		public function update_month_finished ($hash_id, $month) {//Updates the category's name
			$month_id = $this->select_month_id($month); //this will select the id of the category.
			$sql = "UPDATE add_book
			JOIN users ON user_id = users.id
			JOIN month_finished ON month_id = month_finished.id
			SET month_id = ? WHERE hash_id = ?;";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$month_id, $hash_id]);

			return true;
		}

		public function update_classification($hash_id, $classification){
			$sql = "UPDATE add_book SET classification = ? WHERE hash_id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$classification, $hash_id]);

			return true;
		}


		public function get_last_year($user_id){
			$sql = "SELECT DISTINCT year_number FROM add_book
				JOIN users ON user_id = users.id
				JOIN categories ON catg_id = categories.id
				JOIN month_finished ON month_id = month_finished.id
				JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? ORDER BY year_number DESC;"; //This will display only the years where the user added books into his/her list!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id]);
			$result = $stmt->fetch();

			return $result['year_number'];
		}

		public function delete_book($hash_id){
			$sql = "SELECT add_book.id AS add_book_id, book_title, author_name, month_name, year_id, year_number, user_id FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE hash_id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$hash_id]);
			$result = $stmt->fetch();
			$book_title = $result['book_title'];
			$author_name = $result['author_name'];
			$user_id = $result['user_id'];
			$year = $result['year_number'];
			$year_id = $result['year_id'];

			$add_book_id = $result['add_book_id']; //add_book id got from hash_id

			//Deleting add_book item
			$sql = "DELETE FROM add_book WHERE hash_id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$hash_id]);

			//Since I excluded the author's and book's table, I didn't needed the functionality it was here. So I excluded them.

			//Getting all the entries of book with that year_id
			$query_year = "SELECT * FROM add_book WHERE year_id = ?";
			$var_year = $this->connect()->prepare($query_year);
			$var_year->execute([$year_id]);
			$res_year = $var_year->fetchAll();




			if(!count($res_year)>0){/* If there is another add_book item with that particular year added, it will do nothing. Otherwise, it will delete the year from the database*/

				$sql_del_year = "DELETE FROM year_finished WHERE id = ?";
				$stmt_year = $this->connect()->prepare($sql_del_year);
				$stmt_year->execute([$year_id]);
			}

			$file_path = '/var/www/html/booked/bookcovers/bookcover'.$add_book_id.'.jpg';
			//Deleting the bookcover.png file
			if(file_exists($file_path)){ //If the file exists
				unlink($file_path); //delete it
			}

			return $this->get_last_year($user_id); //This was what fixed it! It gets the last year the user has on its account so that it can be redirected later on.

			//I need to create a way so that if the book is the only on in the year, when it is deleted the year that goes to the url needs to be different. Using the function get_last_year I managed to fix the problem!(24/06/19)



		}


		public function download_book_cover_edit($add_book_id){
			$sql = "SELECT book_id, book_title, author_name, month_name, year_number FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE add_book.id = ?";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$add_book_id]);
			$result = $stmt->fetch();

			$book_title = $result['book_title'];
			$author_name = $result['author_name'];



			shell_exec('python3 /var/www/html/booked/python/get_book_url_img.py "'.$book_title.'" "'.$author_name.'" "'.$add_book_id.'" ');
			//I think I finally got it. The problem was because in the download cover function the path of the python script was one folder directory behind the initial page file. But, they are in the same page.


		}
		public function display_months_sidebar($id, $year){
			$url_path = $_SERVER['PHP_SELF'];

			$data = new BookEvent();

?>
			<div class="months">
				<p class="books_year">All year: <?php echo $data->books_read_year($id, $year);?> books!</p>
				<ul id="month_names">

					<li><a class="January" href="<?php echo $url_path.'?year='.$year.'&month=january'; ?>">January: <?php echo $data->books_read_month($id, $year, 'January'); ?></a></li>
					<li><a class="February" href="<?php echo $url_path.'?year='.$year.'&month=february'; ?>">February: <?php echo $data->books_read_month($id, $year, 'February'); ?></a></li>
					<li><a class="March" href="<?php echo $url_path.'?year='.$year.'&month=march'; ?>">March: <?php echo $data->books_read_month($id, $year, 'March'); ?></a></li>
					<li><a class="April" href="<?php echo $url_path.'?year='.$year.'&month=april'; ?>">April: <?php echo $data->books_read_month($id, $year, 'April'); ?></a></li>
					<li><a class="May" href="<?php echo $url_path.'?year='.$year.'&month=may'; ?>">May: <?php echo $data->books_read_month($id, $year, 'May'); ?></a></li>
					<li><a class="June" href="<?php echo $url_path.'?year='.$year.'&month=june'; ?>">June: <?php echo $data->books_read_month($id, $year, 'June'); ?></a></li>
					<li><a class="July" href="<?php echo $url_path.'?year='.$year.'&month=july'; ?>">July: <?php echo $data->books_read_month($id, $year, 'July'); ?></a></li>
					<li><a class="August" href="<?php echo $url_path.'?year='.$year.'&month=august'; ?>">August: <?php echo $data->books_read_month($id, $year, 'August'); ?></a></li>
					<li><a class="September" href="<?php echo $url_path.'?year='.$year.'&month=september'; ?>">September: <?php echo $data->books_read_month($id, $year, 'September'); ?></a></li>
					<li><a class="October" href="<?php echo $url_path.'?year='.$year.'&month=october'; ?>">October: <?php echo $data->books_read_month($id, $year, 'October'); ?></a></li>
					<li><a class="November" href="<?php echo $url_path.'?year='.$year.'&month=november'; ?>">November: <?php echo $data->books_read_month($id, $year, 'November'); ?></a></li>
					<li><a class="December" href="<?php echo $url_path.'?year='.$year.'&month=december'; ?>">December: <?php echo $data->books_read_month($id, $year, 'December'); ?></a></li>
				</ul>
			</div>
<?php
		}


		public function display_months(){
			$sql = "SELECT * FROM month_finished";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll();

			echo'<select name="month" class="month_nav">';
			foreach($result as $data){

				echo'<option value="'.$data['month_name'].'">'.$data['month_name'].'</option>';
			}

			echo '</select>';

		}
		public function display_months_edit($hash_id){
			//Getting the month the user read the edited book from the DB;
			$sql = "SELECT * FROM add_book WHERE hash_id = ?"; //Searching for the id.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$hash_id]);
			$result = $stmt->fetch();

			$month_id = $result['month_id'];//

			$sql2 = "SELECT * FROM month_finished WHERE id = ?";//Searching for the month name.
			$stmt2 = $this->connect()->prepare($sql2);
			$stmt2->execute([$month_id]);
			$result2 = $stmt2->fetch();
			$month_name = $result2['month_name'];

			//Getting all the months from the DB to be displayed in the edit page
			$sql3= "SELECT * FROM month_finished";
			$stmt3 = $this->connect()->prepare($sql3);
			$stmt3->execute();
			$result3 = $stmt3->fetchAll();



			echo'<select name="month" class="month_edit">';
			foreach($result3 as $data){
				if($data['month_name'] == $month_name){

					echo'<option selected value="'.$data['month_name'].'">'.$data['month_name'].'</option>';
				}
				else{
					echo'<option value="'.$data['month_name'].'">'.$data['month_name'].'</option>';
				}

			}

			echo '</select>';
		}

		public function find_year($year){
			$sql = "SELECT * FROM year_finished WHERE year_number = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$year]);
			$result = $stmt->fetch();

			if(empty($result)){
				return false;
			}
			else{
				return true;
			}

		}


		public function last_reading_event($user_id){

			$sql = "SELECT add_book.id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, book_cover_url FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ?ORDER BY id DESC;"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id]);
			$result = $stmt->fetch();

			return $result;


		}

		public function total_books($user_id){
			$sql = "SELECT COUNT(*) as number FROM add_book WHERE user_id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id]);
			$number = $stmt->fetch();

			return $number['number'];
		}

		public function books_read_current_month($user_id, $month_name, $year_number){
			$sql = "SELECT COUNT(*) as number FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND month_name = ? AND year_number = ? ORDER BY add_book.id DESC";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$user_id, $month_name, $year_number]);
			$result = $stmt->fetch();

			return $result['number'];

		}

		public function showUniqueBook($hash_id){
			$sql = "SELECT add_book.id as add_book_id, book_title, author_name, catg_name, month_name, year_number, task_date, classification, hash_id FROM add_book JOIN users ON user_id = users.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE hash_id = ?"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$hash_id]);
			$data = $stmt->fetch();
			if(empty($data)){
				return False;
			}
			else{?>

			<div class="box" value="<?php echo $data['month_name'] ?>">


			<?php
			$book = new BookEvent();
			$location = $book->get_book_cover_url($data['hash_id']);


			echo '<img class="cover" src="'.$location.'">';
			?>
			<div class="book_info">
				<p class="title"><?php echo $data['book_title']; ?></p> <br>
				<p class="author">Author: <span><?php echo $data['author_name']; ?></span></p>
				<p class="category">Category: <span><?php echo $data['catg_name']; ?></span></p>
				<p class="month">Month finished: <span><?php echo $data['month_name']; ?></span></p>
				<p class="date">Date added: <span><?php echo $data['task_date']; ?></span></p>

				<div class="rating">
					<p><span class="grade"><?php echo $data['classification']?></span>%</p>
				</div>
			</div>
			<div><!-- The data goes from here to the javascript.js file and then I use AJAX to pass the data to an includes file called delete_book.php and from there the book is deleted! -->
				<input hidden class="delete_book_input" value="<?php echo $data['hash_id'] ?>">
				<button class="edit_book_button" onclick="editReadingEvent('<?php echo $data['hash_id']; ?>')"><img alt="edit books' information" src="images/edit.png"></button>
				<button onclick="deleteBook('<?php echo $data['hash_id']; ?>', <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash-can.svg"></button>
			</div>


			</div>

<?php
			}
		}
	}
