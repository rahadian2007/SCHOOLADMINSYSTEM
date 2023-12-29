<select class="form-control" id="{{ $id }}">
  <option value="" disabled selected>
    {{ isset($placeholder) ? $placeholder : 'Select your option' }}
  </option>
  {{ $slot }}
</select>