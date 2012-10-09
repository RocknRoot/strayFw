<form method="POST" action="<?php echo $this->vars->formDestination; ?>"
      name="<?php echo $this->vars->formName; ?>"
      id="form<?php echo ucfirst($this->vars->formName); ?>">
  <?php
  if (false === empty($this->vars->formNotices))
    foreach ($this->vars->formNotices as $elem)
      echo '<p class="formNotice">' . $elem . '</p>';
  ?>

  <p class="formParagraph">
    <?php
    foreach ($this->vars->formFields->Get() as $elem)
    {
      echo '<span class="formInputDiv">';
      if (false === empty($elem->notices))
        foreach ($elem->notices as $notice)
          echo '<span class="formNotice">' . $notice . '</span>';
      $elem->Render();
      echo '</span>' . PHP_EOL;
    }
    ?>
  </p>
</form>
