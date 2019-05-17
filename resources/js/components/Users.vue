<template>
    <div class="container">
        <div class="row">
          <div class="col-12">
            <!-- <img src="images/click_here_2li1.svg" style="max-height: 200px;"> -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Responsive Hover Table</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-primary btn-sm" @click="addUserModal">
                      <i class="fa fa-user-plus"></i>
                  </button> <!-- data-toggle="modal" data-target="#addUserModal" -->
                  <!-- <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                    </div>
                  </div> -->
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                 <thead>
                  <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Photo</th>
                    <th>Created</th>
                    <th>Action</th>
                  </tr>
                 </thead>
                 <tbody>
                  <tr v-for="user in users.data" :key="user.id">
                    <td>{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td><img :src="getUserProfilePhoto(user.image)" class="img-responsive" style="max-height: 50px; width: auto;"></td>
                    <td>{{ user.created_at | date }}</td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm" @click="editUserModal(user)">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button @click="deleteUser(user.id)" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                  </tr>
                  
                 </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="users" @pagination-change-page="getPaginationResults"></pagination>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>

        <!-- The Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <!-- Modal Header -->
              <div class="modal-header">
                <h4 v-show="editmode" class="modal-title" id="addUserModalLabel">Update User</h4>
                <h4 v-show="!editmode" class="modal-title" id="addUserModalLabel">Add New User</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <form @submit.prevent="editmode ? updateUser() : createUser()" @keydown="form.onKeydown($event)">
                  <!-- Modal body -->
                  <div class="modal-body">
                        <div class="form-group">
                          <input v-model="form.name" type="text" name="name" placeholder="Name" 
                            class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                          <has-error :form="form" field="name"></has-error>
                        </div>
                        <div class="form-group">
                          <input v-model="form.email" type="text" name="email" placeholder="Email" 
                            class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                          <has-error :form="form" field="email"></has-error>
                        </div>
                        <div class="form-group">
                          <input type="file" v-on:change="uploadImage" name="image" placeholder="Image" 
                            class="form-control" :class="{ 'is-invalid': form.errors.has('image') }">
                          <has-error :form="form" field="image"></has-error>
                        </div>
                        <div class="form-group">
                          <input v-model="form.password" type="password" name="password" placeholder="Password" 
                            class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                          <has-error :form="form" field="password"></has-error>
                        </div>
                        <center>
                          <img :src="getProfilePhotoOnModal()" class="img-responsive" style="max-height: 200px; width: auto;">
                        </center>
                    
                  </div>
                  <!-- Modal footer -->
                  <div class="modal-footer">
                    <button v-show="editmode" type="submit" class="btn btn-success">Update</button>
                    <button v-show="!editmode" type="submit" class="btn btn-success">Submit</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                  </div>
              </form>
            </div>
          </div>
        </div>
    </div>
</template>

<script>
    export default {
        data () {
            return {
              users: {},
              // Create a new form instance
              form: new Form({
                id: '',
                name: '',
                email: '',
                image: '',
                password: ''
              }),
              editmode: false
            }
        },
        methods: {
            addUserModal() {
                this.editmode = false;
                this.form.reset();
                this.image = 'rifat';
                $('#addUserModal').modal('show');
            },
            editUserModal(user) {
                this.editmode = true;
                this.form.reset(); // clears fields
                this.form.clear(); // clears errors
                $('#addUserModal').modal('show');
                this.form.fill(user);
            },
            loadUsers() {
                axios.get('api/user').then(({ data }) => (this.users = data));
            },
            createUser() {
                this.$Progress.start();
                this.form.post('api/user').then(() => {
                    $('#addUserModal').modal('hide')
                    Fire.$emit('AfterCreated')
                    toast.fire({
                      type: 'success',
                      title: 'User created successfully'
                    })
                    this.$Progress.finish();
                })
                .catch(() => {
                    this.$Progress.fail();
                })
            },
            updateUser() {
                this.$Progress.start();
                this.form.put('api/user/'+ this.form.id).then(() => {
                    $('#addUserModal').modal('hide')
                    Fire.$emit('AfterCreated')
                    toast.fire({
                      type: 'success',
                      title: 'User updated successfully'
                    })
                    this.$Progress.finish();
                })
                .catch(() => {
                    this.$Progress.fail();
                    // swal('Failed!', 'There was something wrong', 'warning');
                })
            },
            deleteUser(id) {
                swal.fire({
                  title: 'Are you sure?',
                  text: "You won't be able to revert this!",
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                       this.form.delete('api/user/'+ id).then(() => {
                         swal.fire(
                          'Deleted!',
                          'User has been deleted.',
                          'success'
                          )
                         Fire.$emit('AfterCreated')
                       })
                       .catch(() => {
                         swal('Failed!', 'There was something wrong', 'warning');
                       }) 
                    }

                })
            },
            uploadImage(e) {

              let file = e.target.files[0];
              // console.log(file);
              let reader = new FileReader();
              if((file['size'] / 1024) > 250) {
                swal.fire(
                 'Ops!',
                 'The size of the intended file is <b>' + parseInt(file['size'] / 1024) + 'KB</b>, try uploading under <b>250KB</b>!',
                 'warning'
                )
              } else {
                reader.onloadend = (file) => {
                  var img = new Image();
                  img.src = file.target.result;

                  img.onload = function() {
                      if(((this.height/this.width) < 0.9375) || ((this.height/this.width) > 1.07142)) {
                        swal.fire(
                         'Ops!',
                         'The ratio of height and width should be 1:1',
                         'warning'
                        )
                      }
                  };
                  this.form.image = reader.result;
                }
                reader.readAsDataURL(file);
              }
            },
            getProfilePhotoOnModal() {
              if(this.form.image == null) {
                return '/images/profile.png';
              } else {
                if(this.form.image.length > 200) {
                  return this.form.image;
                } else if(this.form.image.length == 0) {
                  return '/images/profile.png';
                } else {
                  return '/images/users/' + this.form.image;
                }
              }
            },
            getUserProfilePhoto(image) {
              if(image == null) {
                return '/images/profile.png';
              } else {
                if(image.length > 0) {
                  return '/images/users/' + image;
                } else {
                  return '/images/profile.png';
                }
              }
            },
            getPaginationResults(page = 1) {
              axios.get('api/user?page=' + page)
              .then(response => {
                this.users = response.data;
              });
            }
        },
        created() {
            this.loadUsers();
            Fire.$on('AfterCreated', () => {
                this.loadUsers();
            });

            Fire.$on('searching', () => {
                let query = this.$parent.search;
                if(query != '') {
                  axios.get('api/searchuser/' + query)
                  .then((data) => {
                    this.users = data.data;
                  })
                  .catch(() => {

                  })
                } else {
                  this.loadUsers();
                }
                
            });
        }
    }
</script>
