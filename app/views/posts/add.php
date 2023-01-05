<?php require APPROOT . '/views/inc/header.php'; ?>

      <a href="<?php URLROOT ?>/posts" class="btn btn-light"><i class="fa-solid fa-backward"></i> Back</a>
      <div class="card card-body bg-light mt-5">
        <h2>Add Post</h2>
        <p>Please fill in the form.</p>
        <form action="<?php echo URLROOT ?>/posts/add" method="POST">
          <!-- title field -->
          <div class="form-group">
            <label for="email">Title: <sup>*</sup></label>
            <input type="text" name="title" class="form-control form-control-lg <?php echo (!empty($data['title_err'])) ? 'is-invalid' : '' ?>"
              value="<?php echo $data['title'] ?>"
            >
            <span class="invalid-feedback"><?php echo $data['title_err'] ?></span>
          </div>

          <!-- body field -->
          <div class="form-group">
            <label for="body">Body: <sup>*</sup></label>
            <textarea name="body" class="form-control form-control-lg <?php echo (!empty($data['body_err'])) ? 'is-invalid' : '' ?>"
            >
              <?php echo $data['body'] ?>
            </textarea>
            <span class="invalid-feedback"><?php echo $data['body_err'] ?></span>
          </div>
          <input type="submit" value="Add Post" class="btn btn-success btn-block">
        </form>
      </div>


<?php require APPROOT . '/views/inc/footer.php'; ?>