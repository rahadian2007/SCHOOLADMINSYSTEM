@foreach($columns as $column)
                            
    @php
        $isInputRecognized = false;
        foreach ($inputOptions as $option) {
            if ($option['value'] === $column['type']) {
                $isInputRecognized = true;
                break;
            }
        }
    @endphp

    @if ($isInputRecognized)
        <div class="form-group">
            <label class="col-form-label">
                {{ $column['name'] }}
            </label>
            @if ($column['type'] == 'checkbox')
                <div class="form-check checkbox">
                    <input class="form-check-input"
                        type="checkbox"
                        value="true"
                        name="{{ $column['column_name'] }}"
                        {{ $column['value'] == 'true' ? 'checked ' : '' }}
                    />
                    <label class="form-check-label">
                        {{ $column['name'] }}
                    </label>
                </div>
            @elseif ($column['type'] == 'radio')
                <div class="form-check">
                    <input class="form-check-input"
                        type="radio"
                        value="true"
                        name="{{ $column['column_name'] }}"
                        {{ $column['value'] == 'true' ? 'checked ' : '' }}
                    />
                    <label class="form-check-label">
                        yes
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input"
                        type="radio"
                        value="false"
                        name="{{ $column['column_name'] }}"
                        {{ $column['value'] == 'false' ? 'checked ' : '' }}
                    />
                    <label class="form-check-label">
                        no
                    </label>
                </div>
            @elseif ($column['type'] == 'relation_select')
                <select name="{{ $column['column_name'] }}" class="form-control">
                @foreach ($relations['relation_' . $column['column_name']] as $relation)
                    @if ($relation->id == $column['value'])
                        <option selected value="{{ $relation->id }}">
                            {{ $relation->name }}
                        </option>
                    @else
                        <option value="{{ $relation->id }}">
                            {{ $relation->name }}
                        </option>
                    @endif
                @endforeach
                </select>
            @elseif ($column['type'] == 'relation_radio')
                @foreach ($relations['relation_' . $column['column_name']] as $relation)
                    <div class="form-check">
                    @if ($relation->id == $column['value'])
                        <input checked
                            class="form-check-input"
                            type="radio"
                            value="{{ $relation->id }}"
                            name="{{ $column['column_name'] }}"
                        />
                    @else
                        <input class="form-check-input"
                            type="radio"
                            value="{{ $relation->id }}"
                            name="{{ $column['column_name'] }}"
                        />
                    @endif
                        <label class="form-check-label">
                            {{ $relation->name }}
                        </label>
                    </div>
                @endforeach
            @elseif ($column['type'] == 'file' || $column['type'] == 'image')
                <input type="file"
                    class="form-control"
                    name="{{ $column['column_name'] }}"
                />

                @if ($column['type'] == 'image' && $column['value'])
                    <img src="{{ $column['value'] }}"
                        class="img-thumbnail mt-2"
                        style="max-width:200px"
                    />
                @endif
            @elseif ($column['type'] == 'text_area')
                <textarea class="form-control"
                    name="{{ $column['column_name'] }}"
                    rows="2"
                >{{ $column['value'] }}</textarea>
            @elseif ($column['type'] == 'text')
                <input class="form-control"
                    type="text"
                    name="{{ $column['column_name'] }}"
                    value="{{ $column['value'] }}"
                />
            @elseif ($column['type'] == 'number')
                <input class="form-control"
                    type="number"
                    name="{{ $column['column_name'] }}"
                    value="{{ $column['value'] }}"
                />
            @endif
        </div>
    @else
        <p>Field "{{ $column['type'] }}" is not recognized</p>
    @endif
    
@endforeach