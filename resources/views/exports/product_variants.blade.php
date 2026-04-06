<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>SKU</th>
      <th>Código</th>
      <th>Producto</th>
      <th>Color</th>
      <th>Talla / Presentación</th>
      <th>Creado</th>
    </tr>
  </thead>
  <tbody>
  @foreach($variants as $v)
    <tr>
      <td>{{ $v->id }}</td>
      <td>{{ $v->sku }}</td>
      <td>{{ $v->barcode }}</td>
      <td>{{ optional($v->product)->name }}</td>
      <td>{{ optional($v->color)->name }}</td>
      <td>{{ optional($v->size)->name }}</td>
      <td>{{ $v->created_at }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
