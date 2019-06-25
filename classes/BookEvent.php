<?php 
	include "Database.php";

	class BookEvent extends Database{
		private $user_id;

		private $book_title;
		private $author_name;
		private $category;
		
		private $book_id;
		private $author_id;
		private $category_id;
		

		private $month;
		private $month_id;
		private $year;
		private $year_id;

		public function add_book_event($user_id, $book_id, $author_id, $category_id, $month_id, $year_id, $task_date){

			$this->user_id = $user_id;
			$this->book_id = $book_id;
			$this->author_id = $author_id;
			$this->category_id = $category_id;
			$this->month_id = $month_id;
			$this->year_id = $year_id;

			$sql = "INSERT INTO add_book (user_id, book_id, author_id, catg_id, month_id, year_id, task_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $this->book_id, $this->author_id, $this->category_id, $this->month_id, $this->year_id, $task_date ]);

		}

		public function add_book_title($book_title){
			$this->book_title = $book_title;

			$sql = "SELECT * FROM books WHERE book_title = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->book_title]);
			$result = $stmt->fetch();

			if(empty($result)){
				$sql2 = "INSERT INTO books (book_title) VALUES (?)";
				$stmt2 = $this->connect()->prepare($sql2);
				$stmt2->execute([$this->book_title]);
			}
		}

		public function add_author($author_name){
			$this->author_name = $author_name;

			$sql = "SELECT * FROM authors WHERE author_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->author_name]);
			$result = $stmt->fetch();

			if(empty($result)){
				$sql2 = "INSERT INTO authors (author_name) VALUES (?)";
				$stmt2 = $this->connect()->prepare($sql2);
				$stmt2->execute([$this->author_name]);
			}

		}

		public function add_category($category){
			$this->category = $category;

			$sql = "SELECT * FROM categories WHERE catg_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->category]);
			$result = $stmt->fetch();

			if(empty($result)){
				$sql2 = "INSERT INTO categories (catg_name) VALUES (?)";
				$stmt2 = $this->connect()->prepare($sql2);
				$stmt2->execute([$this->category]);
			}

		}
		
		public function add_year($year){
			$this->year = $year;

			$sql = "SELECT * FROM year_finished WHERE year_number = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->year]);
			$result = $stmt->fetch();

			if(empty($result)){
				$sql2 = "INSERT INTO year_finished (year_number) VALUES (?)";
				$stmt2 = $this->connect()->prepare($sql2);
				$stmt2->execute([$this->year]);
			}

		}

		public function select_month_id($month){
			$this->month = $month;

			$sql = "SELECT id FROM month_finished WHERE month_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->month]);

			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				echo "There's no data in the DB that match these criteria.";
			}

		}

		public function select_year_id($year){
			$this->year = $year;

			$sql = "SELECT id FROM year_finished WHERE year_number = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->year]);

			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				echo "There's no data in the DB that match these criteria.";
			}
			
		}

		public function select_book_id($book_title){
			$this->book_title = $book_title;

			$sql = "SELECT id FROM books WHERE book_title = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->book_title]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				echo "There's no data in the DB that match these criteria.";
			}
		}

		public function select_author_id($author_name){
			$this->author_name = $author_name;
			$sql = "SELECT id FROM authors WHERE author_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->author_name]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				echo "There's no data in the DB that match these criteria.";
			}

		}

		public function select_category_id($category){
			$this->category = $category;

			$sql = "SELECT id FROM categories WHERE catg_name = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->category]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['id'];
			}
			else{
				echo "There's no data in the DB that match these criteria.";
			}
		}

		public function download_book_cover($book, $book_id, $author){
			//Download the book cover into the file "bookcovers" where the user add a new book read.
		shell_exec('python3 /var/www/html/booked/python/get_book_url_img.py "'.$book.'" "'.$author.'" "'.$book_id.'" ');

		}

		//FUNCTIONS USED IN THE index.php page!

		public function display_years_homepage($user_id){
			$this->user_id = $user_id;

			$sql = "SELECT DISTINCT year_number FROM add_book  
				JOIN users ON user_id = users.id
				JOIN books ON book_id = books.id
				JOIN authors ON author_id = authors.id
				JOIN categories ON catg_id = categories.id
				JOIN month_finished ON month_id = month_finished.id
				JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? ORDER BY year_number;"; //This will display only the years where the user added books into his/her list!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id]);
			$result = $stmt->fetchAll();
			
			foreach($result as $data){
				echo "<a id='year-unit' href='initial_page.php?year=".$data['year_number']."' onclick='giveId()'>".$data['year_number']." </a>";
			}
			
			

		}

		public function display_years_input($user_id){
			$this->user_id = $user_id;

			$sql = "SELECT DISTINCT year_number FROM add_book  
				JOIN users ON user_id = users.id
				JOIN books ON book_id = books.id
				JOIN authors ON author_id = authors.id
				JOIN categories ON catg_id = categories.id
				JOIN month_finished ON month_id = month_finished.id
				JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? ORDER BY year_number;"; //This will display only the years where the user added books into his/her list!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id]);
			$result = $stmt->fetchAll();
			echo "<div class='years'>";
			foreach($result as $data){
				echo "<a id='year-unit' href='initial_page.php?year=".$data['year_number']."' onclick='giveId()'>".$data['year_number']."</a>";
			}
			echo "</div>";

		}

		public function get_smallest_year(){
			$sql = "SELECT MIN(year_number) AS smallest_year FROM year_finished";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch();
			return $result['smallest_year'];
		}

		public function display_books_year($user_id, $year){
			$this->user_id = $user_id;
			$this->year = $year;

			$sql = "SELECT add_book.id, book_title, book_id, author_name, catg_name, month_name, year_number, task_date, classification FROM add_book JOIN users ON user_id = users.id JOIN books ON book_id = books.id JOIN authors ON author_id = authors.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND year_number = ? ORDER BY month_id;"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $this->year]);
			$result = $stmt->fetchAll();
			
			echo '<p style="color: white; font-size:30px; margin:0 0 5px 15px">Books read in '.$result[0]['year_number'].':</p>';//Just got the first entry and took the year it was added. Since all have the same year, that's ok.

			foreach($result as $data){ ?>

			<div class="box">
			<?php $location = 'bookcovers/bookcover'.$data['book_id'].'.jpg'; ?>
			
			<?php 
			if(file_exists($location)==True){
				echo '<img class="cover" src="'.$location.'">';
			}
			else {
				
				echo '<img class="cover" src="bookcovers/default_bookcover.jpg">';
			}
			?>
			<div class="book_info">
				<p class="title"><?php echo $data['book_title']; ?></p> <br>
				<p class="author">Author: <span><?php echo $data['author_name']; ?></span></p>
				<p class="category">Category: <span><?php echo $data['catg_name']; ?></span></p>
				<p class="month">Month finished: <span><?php echo $data['month_name']; ?></span></p>
				<p class="date">Date added: <span><?php echo $data['task_date']; ?></span></p>
			</div>
			
			<?php 

			echo  '<a href="initial_page.php?edit=true&add_book='.$data['id'].'&book_id='.$data['book_id'].'" id="button_books_box">Edit book\'s information</a>';//This is the edit button where the user can adit the information on the books.
			?>
			<div><!-- The data goes from here to the javascript.js file and then I use AJAX to pass the data to an includes file called delete_book.php and from there the book is deleted! -->
				<input hidden class="delete_book_input" value="<?php echo $data['id'] ?>">
				<button onclick="deleteBook(<?php echo $data['id']; ?>, <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash.png" width="30px"></button>
			</div>
	

			</div>
			<?php  
			}

		}

		public function display_books_month($user_id,$month, $year){
			$this->user_id = $user_id;
			$this->year = $year;
			$this->month = $month;

			$sql = "SELECT add_book.id, book_title, book_id, author_name, catg_name, month_name, year_number, task_date, classification FROM add_book JOIN users ON user_id = users.id JOIN books ON book_id = books.id JOIN authors ON author_id = authors.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND year_number = ? AND month_name = ? ORDER BY month_id, add_book.id DESC"; //When I use ORDER BY id DESC that means it will display always the last row added!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $this->year, $this->month]);
			$result = $stmt->fetchAll();

			foreach($result as $data){ ?>

			<div class="box">
			<?php $location = 'bookcovers/bookcover'.$data['book_id'].'.jpg'; ?>
			
			<?php 
			if(file_exists($location)==True){
				echo '<img class="cover" src="'.$location.'">';
			}
			else {
				
				echo '<img class="cover" src="bookcovers/default_bookcover.jpg">';
			}
			?>
			<div class="book_info">
				<p class="title"><?php echo $data['book_title']; ?></p> <br>
				<p class="author">Author: <span><?php echo $data['author_name']; ?></span></p>
				<p class="category">Category: <span><?php echo $data['catg_name']; ?></span></p>
				<p class="month">Month finished: <span><?php echo $data['month_name']; ?></span></p>
				<p class="date">Date added: <span><?php echo $data['task_date']; ?></span></p>
			</div>
			
			<?php 

			echo  '<a href="initial_page.php?edit=true&add_book='.$data['id'].'&book_id='.$data['book_id'].'" id="button_books_box">Edit book\'s information</a>';//This is the edit button where the user can edit the information on the books.
			
			?>
			<div><!-- The data goes from here to the javascript.js file and then I use AJAX to pass the data to an includes file called delete_book.php and from there the book is deleted! -->
				<input hidden class="delete_book_input" value="<?php echo $data['id'] ?>">
				<button onclick="deleteBook(<?php echo $data['id']; ?>, <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash.png" width="30px"></button>
			</div>

			</div>
			<?php  
			}
		}

		public function books_read_month($user_id, $year, $month){
			$this->user_id = $user_id;
			$this->year = $year;
			$this->month = $month;

			$sql = "SELECT COUNT(book_title) AS books_read, month_name, year_number FROM add_book 
			JOIN users ON user_id = users.id 
			JOIN books ON book_id = books.id 
			JOIN authors ON author_id = authors.id 
			JOIN categories ON catg_id = categories.id 
			JOIN month_finished ON month_id = month_finished.id 
			JOIN year_finished ON year_id = year_finished.id 
			WHERE user_id = ? AND year_number = ? AND month_name = ? GROUP BY month_name";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $this->year, $this->month]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['books_read'];
			}
			else{
				return '0';
			}

		}

		public function books_read_year($user_id, $year){
			$this->user_id = $user_id;
			$this->year = $year;

			$sql = "SELECT COUNT(book_title) AS books_read FROM add_book 
			JOIN users ON user_id = users.id 
			JOIN books ON book_id = books.id 
			JOIN authors ON author_id = authors.id 
			JOIN categories ON catg_id = categories.id 
			JOIN month_finished ON month_id = month_finished.id 
			JOIN year_finished ON year_id = year_finished.id 
			WHERE user_id = ? AND year_number = ? GROUP BY year_number";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $this->year]);
			$result = $stmt->fetch();
			if(!empty($result)){
				return $result['books_read'];
			}
			else{
				return '0';
			}


		}

		public function search($user_id, $search_term){
			$this->user_id = $user_id;

			$sql = "SELECT add_book.id, book_title, book_id, author_name, catg_name, month_name, year_number, task_date FROM add_book 
			JOIN users ON user_id = users.id 
			JOIN books ON book_id = books.id 
			JOIN authors ON author_id = authors.id 
			JOIN categories ON catg_id = categories.id 
			JOIN month_finished ON month_id = month_finished.id 
			JOIN year_finished ON year_id = year_finished.id 
			WHERE user_id = ? HAVING book_title LIKE ? OR author_name LIKE ? 
			OR catg_name LIKE ? ORDER BY year_id DESC";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $search_term, $search_term, $search_term]);
			$result = $stmt->fetchAll();

			foreach($result as $data){ ?>

			<div class="box">
			<?php $location = 'bookcovers/bookcover'.$data['book_id'].'.jpg'; ?>
			
			<?php 
			if(file_exists($location)==True){
				echo '<img class="cover" src="'.$location.'">';
			}
			else {
				
				echo '<img class="cover" src="bookcovers/default_bookcover.jpg">';
			}
			?>
			<div class="book_info">
				<p class="title"><?php echo $data['book_title']; ?></p>
				<p class="author">Author: <?php echo $data['author_name']; ?></p>
				<p class="category">Category: <?php echo $data['catg_name']; ?></p>
				<p class="month">Month finished: <?php echo $data['month_name']; ?></p>
				<p class="year">Year finished: <?php echo $data['year_number']; ?></p>
				<p class="date">Date added: <?php echo $data['task_date']; ?></p>
			</div>
			
			<?php 

			echo  '<a href="initial_page.php?edit=true&add_book='.$data['id'].'&book_id='.$data['book_id'].'" id="button_books_box">Edit book\'s information</a>';//This is the edit button where the user can adit the information on the books.
			?>
				<input hidden class="delete_book_input" value="<?php echo $data['id'] ?>">
				<button onclick="deleteBook(<?php echo $data['id']; ?>, <?php echo $data['year_number']; ?>)" class="delete_book_button"><img src="images/trash.png" width="30px"></button>
		

			</div>
			<?php  
			}
		}

		public function display_edit_book($user_id, $book_id, $add_book_id, $location){
			$this->user_id = $user_id;
			$this->book_id = $book_id;

			$sql = "SELECT add_book.id, book_title, book_id, author_name, catg_name, month_name, year_number, task_date FROM add_book JOIN users ON user_id = users.id JOIN books ON book_id = books.id JOIN authors ON author_id = authors.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND add_book.id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $add_book_id]);
			$result = $stmt->fetchAll();

			foreach($result as $data){
			?> 
			<div class="box_edit">

				<?php
				if(file_exists($location)==True){
					echo '<img class="edit_cover" src="'.$location.'">';
				}
				else {
					
					echo '<img class="edit_cover" src="bookcovers/default_bookcover.jpg">';
				}
				?>
				
				<form class="edit_info" method="POST" action="includes/edit_book.php">
					<p class="title_text">Title: </p>
					<?php echo ' <input class="edit_title_name" type="text" name="book_title" value="'.$data["book_title"].'"><br> '?>
					<p class="author_text">Author: </p>
					<?php echo ' <input class="edit_authors_name" type="text" name="author_name" value="'.$data["author_name"].'"><br>  '?>
					<p class="category_text">Category:</p>
					<?php echo ' <input class="edit_category_name" type="text" name="catg_name" value="'.$data["catg_name"].'"><br>  '?>
					<p class="month_text">Month finished:</p><br>
					<?php 
						$month = new BookEvent();
						echo $month->display_months_edit($add_book_id);?><br>
					<p class="year_text">Year finished:</p>
					<?php echo ' <input class="edit_year_number" type="text" name="year_number" value="'.$data["year_number"].'"><br> '?>
					<?php echo ' <input style="display:none" name="add_book_id" value="'.$add_book_id.'"> ' //In here a created a input that stores the add_book_id. It is then passed to the edit_book.php file to update the values. ?>
					<?php echo ' <input style="display:none" name="book_id" value="'.$this->book_id.'"> ' //In here a created a input that stores the book id. It is then passed to the edit_book.php file to update the values.?>
					<button type="submit" class="save_button_edit">Save changes</button><!--//This is the button that when clicked will lead the user into the update.php file (when all the database interaction will be done) and then back to the initial page.-->
					<div>
						<input hidden class="add_book_id_input" value="<?php echo $add_book_id ?>" name="add_book_id" type="text">
						<input hidden class="book_id_input" value="<?php echo $book_id ?>" name="book_id" type="text">
						<button class="delete_cover">Delete book cover</button>
					</div>
					
					<?php echo '<a style="cursor: pointer"><img id="add_cover" src="images/plus.png"></a>'; ?>
				</form>

			</div>
			<?php
			
			}
		}
		public function get_last_year($user_id){
			$this->user_id = $user_id;

			$sql = "SELECT DISTINCT year_number FROM add_book  
				JOIN users ON user_id = users.id
				JOIN books ON book_id = books.id
				JOIN authors ON author_id = authors.id
				JOIN categories ON catg_id = categories.id
				JOIN month_finished ON month_id = month_finished.id
				JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? ORDER BY year_number DESC;"; //This will display only the years where the user added books into his/her list!
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id]);
			$result = $stmt->fetch();
			
			return $result['year_number'];
		}

		public function delete_book($add_book_id){
			$sql = "SELECT book_id, author_id, year_id, book_title, author_name, month_name, year_number, user_id FROM add_book JOIN users ON user_id = users.id JOIN books ON book_id = books.id JOIN authors ON author_id = authors.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE add_book.id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$add_book_id]);
			$result = $stmt->fetch();
			$this->book_title = $result['book_title'];
			$this->author_id = $result['author_id'];
			$this->book_id = $result['book_id'];
			$this->user_id = $result['user_id'];
			$this->year = $result['year_number'];
			$this->year_id = $result['year_id'];

			//Deleting add_book item
			$sql = "DELETE FROM add_book WHERE id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$add_book_id]);

			
			//Getting all the entries of book with that book Id.
			$query_book = "SELECT * FROM add_book WHERE book_id = ?";
			$var_book = $this->connect()->prepare($query_book);
			$var_book->execute([$this->book_id]);
			$res_book = $var_book->fetchAll();
		
			//Getting the entry of the author of the add_book item
			$query_author = "SELECT * FROM add_book WHERE author_id = ?";
			$var_author = $this->connect()->prepare($query_author);
			$var_author->execute([$this->author_id]);
			$res_author = $var_author->fetchAll();
		
			//Getting all the entries of book with that year_id
			$query_year = "SELECT * FROM add_book WHERE year_id = ?";
			$var_year = $this->connect()->prepare($query_year);
			$var_year->execute([$this->year_id]);
			$res_year = $var_year->fetchAll();


			//If there's another add_book with this author, then don't do nothing. Else, delete the author from the database.
			if(!count($res_author) > 0){
				$sql_del_author = "DELETE FROM authors WHERE id = ?";
				$stmt_author = $this->connect()->prepare($sql_del_author);
				$stmt_author->execute([$this->author_id]);

			}
			
			//If there's another add_book with this book, then don't do nothing. Else, delete the book from the database.
			if(!count($res_book)>0){
				$sql_del_book = "DELETE FROM books WHERE id = ?";
				$stmt_book = $this->connect()->prepare($sql_del_book);
				$stmt_book->execute([$this->book_id]);
			}

			if(!count($res_year)>0){
				$sql_del_year = "DELETE FROM year_finished WHERE id = ?";
				$stmt_year = $this->connect()->prepare($sql_del_year);
				$stmt_year->execute([$this->year_id]);
			}

			$file_path = 'bookcovers/bookcover'.$this->book_id.'.jpg';
			//Deleting the bookcover.png file
			if(file_exists($file_path)){ //If the file exists
				unlink($file_path); //delete it
			}
			
		
			$get = new BookEvent(); //I didn't know I had to create a new object even when I'm already inside the class...¯\_(ツ)_/¯
			$last_year = $get->get_last_year($this->user_id); //This was what fixed it! It gets the last year the user has on its account so that it can be redirected later on.
			
			//I need to create a way so that if the book is the only on in the year, when it is deleted the year that goes to the url needs to be different. Using the function get_last_year I managed to fix the problem!(24/06/19)
			return $last_year;
			
		}

		public function delete_book_cover($book_id){
			$this->book_id = $book_id;

			$location = "/var/www/html/booked/bookcovers/bookcover".$this->book_id.".jpg";
			if(file_exists($location)){ //If there's a file with that id, than delete it. If not, nothing happens. This is to prevent the user to click the delete cover button when the book already doesn't have any cover image.
				unlink($location);
				
			}

		}

		public function download_book_cover_edit($add_book_id){
			$sql = "SELECT book_id, book_title, author_name, month_name, year_number FROM add_book JOIN users ON user_id = users.id JOIN books ON book_id = books.id JOIN authors ON author_id = authors.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE add_book.id = ?";

			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$add_book_id]);
			$result = $stmt->fetch();

			$book_title = $result['book_title'];
			$author_name = $result['author_name'];
			$book_id = $result['book_id'];
			

			shell_exec('python3 /var/www/html/booked/python/get_book_url_img.py "'.$book_title.'" "'.$author_name.'" "'.$book_id.'" ');	
			//I think I finally got it. The problem was because in the download cover function the path of the python script was one folder directory behind the initial page file. But, they are in the same page. 
			

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
		public function display_months_edit($reading_event_id){
			//Getting the month the user read the edited book from the DB;
			$sql = "SELECT * FROM add_book WHERE id = ?"; //Searching for the id.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$reading_event_id]);
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

			

			echo'<select name="month" class="month_nav">';
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
			$this->user_id = $user_id;

			$sql = "SELECT add_book.id, book_title, book_id, author_name, catg_name, month_name, year_number, task_date, classification FROM add_book JOIN users ON user_id = users.id JOIN books ON book_id = books.id JOIN authors ON author_id = authors.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ?ORDER BY id DESC;"; //This is how you do it bro. You now order the books by the month the user read the book! And now is in descending order, meaning that the first books are the ones first chronologically.
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id]);
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
			$this->user_id = $user_id;
			$this->month_name = $month_name;
			$this->year_number = $year_number;

			$sql = "SELECT COUNT(*) as number FROM add_book JOIN users ON user_id = users.id JOIN books ON book_id = books.id JOIN authors ON author_id = authors.id JOIN categories ON catg_id = categories.id JOIN month_finished ON month_id = month_finished.id JOIN year_finished ON year_id = year_finished.id WHERE user_id = ? AND month_name = ? AND year_number = ? ORDER BY add_book.id DESC";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute([$this->user_id, $this->month_name, $this->year_number]);
			$result = $stmt->fetch();

			return $result['number'];

		}
	}
