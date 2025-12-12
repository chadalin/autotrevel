<pre>
@foreach($results as $table => $sql)
Table: {{ $table }}
{{ $sql }}

@endforeach
</pre>