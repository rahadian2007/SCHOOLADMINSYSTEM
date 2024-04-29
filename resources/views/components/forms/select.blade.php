<select class="form-control" id="{{ $id }}" {{ $attributes }}>
  <option value="" disabled selected>
    {{ isset($placeholder) ? $placeholder : 'Select your option' }}
  </option>
  {{ $slot }}
</select>